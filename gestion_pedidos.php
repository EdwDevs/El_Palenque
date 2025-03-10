<?php
// gestion_pedidos.php - Panel de administración para gestionar pedidos
// Autor: [Tu Nombre]
// Fecha: [Fecha actual]
// Descripción: Este archivo permite a los administradores ver, filtrar y gestionar todos los pedidos del sistema

// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Verificar si el usuario está autenticado; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Verificar si el usuario tiene rol de administrador; si no, redirigir a la página de usuario estándar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: user_home.php");
    exit();
}

// Almacenar el nombre del usuario logueado en una variable con seguridad contra XSS
$username = htmlspecialchars($_SESSION['usuario']);

// Incluir el archivo de conexión a la base de datos
include('db.php');

// Manejar la eliminación de pedidos si se proporciona un ID
if (isset($_GET['eliminar_pedido'])) {
    $pedido_id = intval($_GET['eliminar_pedido']);

    try {
        // Iniciar transacción para asegurar la integridad de los datos
        $conexion->begin_transaction();

        // Eliminar registros relacionados en la tabla entregas (si existe)
        $stmt = $conexion->prepare("DELETE FROM entregas WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        // Eliminar detalles del pedido primero
        $stmt = $conexion->prepare("DELETE FROM detalles_pedido WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        // Eliminar el pedido principal
        $stmt = $conexion->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        // Confirmar la transacción
        $conexion->commit();

        // Redirigir con mensaje de éxito
        header("Location: gestion_pedidos.php?mensaje=Pedido eliminado correctamente&tipo=success");
        exit();
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conexion->rollback();

        // Redirigir con mensaje de error
        header("Location: gestion_pedidos.php?mensaje=Error al eliminar el pedido: " . $e->getMessage() . "&tipo=danger");
        exit();
    }
}

// Configuración de filtros y paginación
// =====================================

// Valores predeterminados para filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_desc'; // Orden predeterminado: más recientes primero

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construir la consulta SQL base
$sql_base = "
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    WHERE 1=1
";

// Añadir condiciones de filtro a la consulta
$params = [];
$tipos = "";

// Filtro por estado
if (!empty($filtro_estado)) {
    $sql_base .= " AND p.estado = ?";
    $params[] = $filtro_estado;
    $tipos .= "s";
}

// Filtro por usuario (búsqueda por nombre o ID)
if (!empty($filtro_usuario)) {
    $sql_base .= " AND (u.nombre LIKE ? OR u.id = ?)";
    $params[] = "%$filtro_usuario%";
    $params[] = intval($filtro_usuario);
    $tipos .= "si";
}

// Filtro por rango de fechas
if (!empty($filtro_fecha_desde)) {
    $sql_base .= " AND p.fecha_pedido >= ?";
    $params[] = $filtro_fecha_desde . " 00:00:00";
    $tipos .= "s";
}

if (!empty($filtro_fecha_hasta)) {
    $sql_base .= " AND p.fecha_pedido <= ?";
    $params[] = $filtro_fecha_hasta . " 23:59:59";
    $tipos .= "s";
}

// Determinar el orden de los resultados
$sql_orden = "";
switch ($orden) {
    case 'fecha_asc':
        $sql_orden = " ORDER BY p.fecha_pedido ASC";
        break;
    case 'fecha_desc':
        $sql_orden = " ORDER BY p.fecha_pedido DESC";
        break;
    case 'total_asc':
        $sql_orden = " ORDER BY p.total ASC";
        break;
    case 'total_desc':
        $sql_orden = " ORDER BY p.total DESC";
        break;
    case 'estado':
        $sql_orden = " ORDER BY p.estado ASC, p.fecha_pedido DESC";
        break;
    case 'usuario':
        $sql_orden = " ORDER BY u.nombre ASC, p.fecha_pedido DESC";
        break;
    default:
        $sql_orden = " ORDER BY p.fecha_pedido DESC";
}

// Consulta para contar el total de registros (para la paginación)
$sql_count = "SELECT COUNT(*) as total " . $sql_base;
$stmt_count = $conexion->prepare($sql_count);

// Vincular parámetros si existen
if (!empty($params)) {
    $ref_params = [];
    $ref_params[] = &$tipos;
    foreach ($params as $key => $value) {
        $ref_params[] = &$params[$key];
    }
    call_user_func_array([$stmt_count, 'bind_param'], $ref_params);
}

$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta principal para obtener los pedidos filtrados y paginados
$sql = "SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre " .
    $sql_base . $sql_orden . " LIMIT ?, ?";

$stmt = $conexion->prepare($sql);

// Añadir parámetros de paginación
$params[] = $offset;
$params[] = $registros_por_pagina;
$tipos .= "ii";

// Vincular parámetros
$ref_params = [];
$ref_params[] = &$tipos;
foreach ($params as $key => $value) {
    $ref_params[] = &$params[$key];
}
call_user_func_array([$stmt, 'bind_param'], $ref_params);

$stmt->execute();
$result = $stmt->get_result();

// Obtener lista de estados para el filtro
$estados = ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'];

// Obtener lista de usuarios para el filtro autocompletable
$stmt_usuarios = $conexion->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC");
$usuarios = [];
while ($row = $stmt_usuarios->fetch_assoc()) {
    $usuarios[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración para gestionar pedidos de Sabor Colombiano">
    <title>Gestión de Pedidos - Sabor Colombiano</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Flatpickr (para selector de fechas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            --color-primary: #FF5722;
            --color-secondary: #4CAF50;
            --color-accent: #FFC107;
            --color-text: #333333;
            --color-light: #FFFFFF;
            --color-hover: #FFF3E0;
            --color-danger: #f44336;
            --color-danger-dark: #d32f2f;
            --color-success: #4CAF50;
            --color-info: #2196F3;
            --color-warning: #FF9800;
            --border-radius: 10px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition-normal: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: var(--color-text);
            padding-bottom: 60px;
            position: relative;
        }

        /* Header modernizado */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.8rem 0;
            box-shadow: var(--box-shadow);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition-normal);
        }

        .header-logo {
            margin-left: 2rem;
        }

        .header-logo img {
            max-width: 120px;
            border-radius: 50%;
            border: 3px solid var(--color-primary);
            transition: transform 0.3s ease;
            object-fit: cover;
        }

        .header-logo img:hover {
            transform: scale(1.05);
        }

        .user-welcome {
            color: var(--color-primary);
            font-weight: 600;
            font-size: 1.1rem;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            margin-right: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
        }

        .user-welcome i {
            margin-right: 0.5rem;
            color: var(--color-secondary);
        }

        .header-actions {
            display: flex;
            gap: 0.8rem;
            margin-right: 2rem;
        }

        /* Botones modernizados */
        .btn-nav {
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 30px;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--color-light);
            background-color: var(--color-primary);
        }

        .btn-nav:hover {
            background-color: var(--color-secondary);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: var(--color-light);
        }

        /* Contenedor principal mejorado */
        .container {
            margin-top: 8rem;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            max-width: 1200px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Título con diseño moderno */
        h1 {
            color: var(--color-primary);
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            padding-bottom: 0.8rem;
            font-size: 2.2rem;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
            border-radius: 4px;
        }

        /* Alerta mejorada */
        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.15);
            color: var(--color-secondary);
            border-left: 4px solid var(--color-secondary);
        }

        .alert-success::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 1.2rem;
            color: var(--color-secondary);
        }

        .alert-danger {
            background-color: rgba(244, 67, 54, 0.15);
            color: var(--color-danger);
            border-left: 4px solid var(--color-danger);
        }

        .alert-danger::before {
            content: '\f057';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 1.2rem;
            color: var(--color-danger);
        }

        /* Sección de filtros */
        .filtros-container {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 193, 7, 0.2);
        }

        .filtros-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px dashed rgba(255, 193, 7, 0.3);
        }

        .filtros-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--color-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filtros-title i {
            color: var(--color-secondary);
        }

        .filtros-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .filtro-grupo {
            flex: 1;
            min-width: 200px;
        }

        .filtro-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--color-text);
            font-size: 0.9rem;
        }

        .filtro-input {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: var(--transition-normal);
        }

        .filtro-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
            outline: none;
        }

        .filtro-select {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: var(--transition-normal);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23FF5722' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }

        .filtro-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
            outline: none;
        }

        .filtro-date {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: var(--transition-normal);
        }

        .filtro-date:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
            outline: none;
        }

        .filtros-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .btn-filtrar {
            background-color: var(--color-secondary);
            color: var(--color-light);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-filtrar:hover {
            background-color: #3d8b40;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-limpiar {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--color-accent);
            border: 1px solid rgba(255, 193, 7, 0.3);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-limpiar:hover {
            background-color: rgba(255, 193, 7, 0.2);
            transform: translateY(-2px);
        }

        /* Resumen de filtros aplicados */
        .filtros-aplicados {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed rgba(255, 193, 7, 0.3);
        }

        .filtro-tag {
            background-color: rgba(255, 87, 34, 0.1);
            color: var(--color-primary);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filtro-tag i {
            font-size: 0.75rem;
        }

        .filtro-tag-remove {
            background: none;
            border: none;
            color: var(--color-primary);
            cursor: pointer;
            padding: 0;
            margin-left: 0.3rem;
            opacity: 0.7;
            transition: var(--transition-normal);
        }

        .filtro-tag-remove:hover {
            opacity: 1;
        }

        /* Información de resultados y ordenación */
        .resultados-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .resultados-contador {
            font-size: 0.95rem;
            color: var(--color-text);
        }

        .resultados-contador strong {
            color: var(--color-primary);
        }

        .ordenar-por {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .ordenar-label {
            font-size: 0.95rem;
            color: var(--color-text);
            margin: 0;
        }

        .ordenar-select {
            padding: 0.4rem 0.8rem;
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: var(--transition-normal);
            font-size: 0.9rem;
        }

        .ordenar-select:focus {
            border-color: var(--color-primary);
            outline: none;
        }

        /* Tabla modernizada */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--color-secondary);
            color: var(--color-light);
            font-weight: 600;
            padding: 1.2rem 1rem;
            border: none;
            text-align: center;
            position: sticky;
            top: 0;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
        }

        .table thead th:first-child {
            border-top-left-radius: var(--border-radius);
        }

        .table thead th:last-child {
            border-top-right-radius: var(--border-radius);
        }

        .table tbody tr {
            transition: var(--transition-normal);
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(76, 175, 80, 0.05);
        }

        .table tbody tr:hover {
            background-color: var(--color-hover);
            transform: scale(1.01);
        }

        .table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(255, 193, 7, 0.2);
            text-align: center;
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Celdas especiales */
        .id-cell {
            font-weight: 600;
            color: var(--color-primary);
            background-color: rgba(255, 87, 34, 0.05);
            border-radius: 8px;
            padding: 0.4rem 0.8rem;
            display: inline-block;
        }

        .user-cell {
            font-weight: 600;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .user-cell i {
            color: var(--color-secondary);
            background-color: rgba(76, 175, 80, 0.1);
            padding: 0.5rem;
            border-radius: 50%;
        }

        .date-cell {
            color: #666;
            font-size: 0.9rem;
        }

        .price-cell {
            font-weight: 700;
            color: var(--color-primary);
        }

        .status-cell {
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .status-pendiente {
            background-color: rgba(255, 87, 34, 0.1);
            color: var(--color-primary);
        }

        .status-pendiente::before {
            content: '\f017';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .status-confirmado {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--color-secondary);
        }

        .status-confirmado::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .status-enviado {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--color-accent);
        }

        .status-enviado::before {
            content: '\f0d1';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .status-entregado {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--color-secondary);
        }

        .status-entregado::before {
            content: '\f5b0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .status-cancelado {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--color-danger);
        }

        .status-cancelado::before {
            content: '\f00d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.5rem;
        }

        .actions-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .btn-action {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
            border: none;
        }

        .btn-ver {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--color-accent);
        }

        .btn-ver:hover {
            background-color: var(--color-accent);
            color: var(--color-text);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-estado {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--color-secondary);
        }

        .btn-estado:hover {
            background-color: var(--color-secondary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-eliminar {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--color-danger);
        }

        .btn-eliminar:hover {
            background-color: var(--color-danger);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Footer modernizado */
        footer {
            text-align: center;
            padding: 1.2rem;
            color: var(--color-text);
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(255, 193, 7, 0.2);
            font-family: 'Montserrat', sans-serif;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .container {
                padding: 2rem;
                margin-top: 7.5rem;
            }

            h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 0.8rem 0;
            }

            .header-logo {
                margin: 0.5rem 0;
            }

            .header-logo img {
                max-width: 80px;
            }

            .user-welcome {
                margin: 0.5rem 0;
                font-size: 1rem;
            }

            .header-actions {
                margin: 0.5rem 0;
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                margin-top: 12rem;
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .btn-nav {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .actions-cell {
                flex-direction: column;
                gap: 0.4rem;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 1rem;
                margin-top: 14rem;
            }

            .table td,
            .table th {
                padding: 0.8rem 0.5rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <!-- Encabezado fijo con logo, bienvenida y botones -->
    <header>
        <div class="header-logo">
            <a href="index.php" title="Ir a la página principal">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>
        <span class="user-welcome">
            <i class="fas fa-user-shield"></i>¡Hola, <?php echo $username; ?>!
        </span>
        <div class="header-actions">
            <a href="admin_home.php" class="btn-nav" title="Gestionar usuarios">
                <i class="fas fa-users"></i>Usuarios
            </a>
            <a href="productos.php" class="btn-nav" title="Gestionar productos">
                <i class="fas fa-shopping-cart"></i>Productos
            </a>
            <a href="logout.php" class="btn-nav" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>Salir
            </a>
        </div>
    </header>

    <!-- Contenedor principal con la tabla de gestión de pedidos -->
    <div class="container">
        <h1>Gestión de Pedidos</h1>

        <!-- Mensaje de feedback -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_GET['tipo']); ?>" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>
        <?php endif; ?>

        <!-- Sección de filtros -->
        <div class="filtros-container">
            <div class="filtros-header">
                <h2 class="filtros-title"><i class="fas fa-filter"></i> Filtrar Pedidos</h2>
            </div>
            <form class="filtros-form" method="GET" action="gestion_pedidos.php">
                <div class="filtro-grupo">
                    <label class="filtro-label" for="estado">Estado:</label>
                    <select class="filtro-select" name="estado" id="estado">
                        <option value="">Todos</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?php echo $estado; ?>" <?php echo ($filtro_estado === $estado) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($estado); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filtro-grupo">
                    <label class="filtro-label" for="usuario">Usuario:</label>
                    <input type="text" class="filtro-input" name="usuario" id="usuario" value="<?php echo htmlspecialchars($filtro_usuario); ?>" placeholder="Nombre o ID de usuario">
                </div>
                <div class="filtro-grupo">
                    <label class="filtro-label" for="fecha_desde">Fecha Desde:</label>
                    <input type="text" class="filtro-date" name="fecha_desde" id="fecha_desde" value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="filtro-grupo">
                    <label class="filtro-label" for="fecha_hasta">Fecha Hasta:</label>
                    <input type="text" class="filtro-date" name="fecha_hasta" id="fecha_hasta" value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="filtros-actions">
                    <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
                    <a href="gestion_pedidos.php" class="btn-limpiar"><i class="fas fa-times"></i> Limpiar</a>
                </div>
            </form>
            <div class="filtros-aplicados">
                <?php if ($filtro_estado): ?>
                    <span class="filtro-tag">
                        Estado: <?php echo ucfirst(htmlspecialchars($filtro_estado)); ?>
                        <button class="filtro-tag-remove" onclick="window.location.href='gestion_pedidos.php?usuario=<?php echo htmlspecialchars($filtro_usuario); ?>&fecha_desde=<?php echo htmlspecialchars($filtro_fecha_desde); ?>&fecha_hasta=<?php echo htmlspecialchars($filtro_fecha_hasta); ?>'">&times;</button>
                    </span>
                <?php endif; ?>
                <?php if ($filtro_usuario): ?>
                    <span class="filtro-tag">
                        Usuario: <?php echo htmlspecialchars($filtro_usuario); ?>
                        <button class="filtro-tag-remove" onclick="window.location.href='gestion_pedidos.php?estado=<?php echo htmlspecialchars($filtro_estado); ?>&fecha_desde=<?php echo htmlspecialchars($filtro_fecha_desde); ?>&fecha_hasta=<?php echo htmlspecialchars($filtro_fecha_hasta); ?>'">&times;</button>
                    </span>
                <?php endif; ?>
                <?php if ($filtro_fecha_desde): ?>
                    <span class="filtro-tag">
                        Fecha Desde: <?php echo htmlspecialchars($filtro_fecha_desde); ?>
                        <button class="filtro-tag-remove" onclick="window.location.href='gestion_pedidos.php?estado=<?php echo htmlspecialchars($filtro_estado); ?>&usuario=<?php echo htmlspecialchars($filtro_usuario); ?>&fecha_hasta=<?php echo htmlspecialchars($filtro_fecha_hasta); ?>'">&times;</button>
                    </span>
                <?php endif; ?>
                <?php if ($filtro_fecha_hasta): ?>
                    <span class="filtro-tag">
                        Fecha Hasta: <?php echo htmlspecialchars($filtro_fecha_hasta); ?>
                        <button class="filtro-tag-remove" onclick="window.location.href='gestion_pedidos.php?estado=<?php echo htmlspecialchars($filtro_estado); ?>&usuario=<?php echo htmlspecialchars($filtro_usuario); ?>&fecha_desde=<?php echo htmlspecialchars($filtro_fecha_desde); ?>'">&times;</button>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información de resultados -->
        <div class="resultados-info">
            <div class="resultados-contador">
                Mostrando <strong><?php echo $total_registros; ?></strong> pedidos
            </div>
            <div class="ordenar-por">
                <label class="ordenar-label" for="orden">Ordenar por:</label>
                <select class="ordenar-select" name="orden" id="orden" onchange="window.location.href='gestion_pedidos.php?estado=<?php echo htmlspecialchars($filtro_estado); ?>&usuario=<?php echo htmlspecialchars($filtro_usuario); ?>&fecha_desde=<?php echo htmlspecialchars($filtro_fecha_desde); ?>&fecha_hasta=<?php echo htmlspecialchars($filtro_fecha_hasta); ?>&orden=' + this.value;">
                    <option value="fecha_desc" <?php echo ($orden === 'fecha_desc') ? 'selected' : ''; ?>>Fecha (más recientes)</option>
                    <option value="fecha_asc" <?php echo ($orden === 'fecha_asc') ? 'selected' : ''; ?>>Fecha (más antiguos)</option>
                    <option value="total_desc" <?php echo ($orden === 'total_desc') ? 'selected' : ''; ?>>Total (mayor a menor)</option>
                    <option value="total_asc" <?php echo ($orden === 'total_asc') ? 'selected' : ''; ?>>Total (menor a mayor)</option>
                    <option value="estado" <?php echo ($orden === 'estado') ? 'selected' : ''; ?>>Estado</option>
                    <option value="usuario" <?php echo ($orden === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                </select>
            </div>
        </div>

        <!-- Tabla responsive de pedidos -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table" id="pedidosTable">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($fila = $result->fetch_assoc()) {
                                $estadoClass = '';
                                switch ($fila['estado']) {
                                    case 'pendiente':
                                        $estadoClass = 'status-pendiente';
                                        break;
                                    case 'confirmado':
                                        $estadoClass = 'status-confirmado';
                                        break;
                                    case 'enviado':
                                        $estadoClass = 'status-enviado';
                                        break;
                                    case 'entregado':
                                        $estadoClass = 'status-entregado';
                                        break;
                                    case 'cancelado':
                                        $estadoClass = 'status-cancelado';
                                        break;
                                }
                        ?>
                                <tr>
                                    <td><span class="id-cell">#<?php echo htmlspecialchars($fila['id']); ?></span></td>
                                    <td class="user-cell">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($fila['usuario_nombre']); ?>
                                    </td>
                                    <td class="date-cell"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($fila['fecha_pedido']))); ?></td>
                                    <td class="price-cell">$<?php echo number_format($fila['total'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-cell <?php echo $estadoClass; ?>">
                                            <?php echo ucfirst(htmlspecialchars($fila['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="ver_pedido.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-ver" title="Ver detalles del pedido">
                                            <i class="fas fa-eye"></i> Ver detalles
                                        </a>
                                        <a href="modificar_estado_pedido.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-estado" title="Modificar estado del pedido">
                                            <i class="fas fa-edit"></i> Cambiar estado
                                        </a>
                                        <a href="gestion_pedidos.php?eliminar_pedido=<?php echo $fila['id']; ?>" class="btn-action btn-eliminar" title="Eliminar pedido" onclick="return confirm('¿Estás seguro de que deseas eliminar este pedido? Esta acción no se puede deshacer.')">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-shopping-basket" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                    No hay pedidos registrados en el sistema.
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                        <a class="page-link" href="gestion_pedidos.php?estado=<?php echo htmlspecialchars($filtro_estado); ?>&usuario=<?php echo htmlspecialchars($filtro_usuario); ?>&fecha_desde=<?php echo htmlspecialchars($filtro_fecha_desde); ?>&fecha_hasta=<?php echo htmlspecialchars($filtro_fecha_hasta); ?>&orden=<?php echo htmlspecialchars($orden); ?>&pagina=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Pie de página -->
    <footer>
        <p>© 2025 Sabor Colombiano - Gestión con raíces y tradición.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Flatpickr JS (para selector de fechas) -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Script personalizado -->
    <script>
        // Inicializar Flatpickr para los campos de fecha
        flatpickr("#fecha_desde", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
        flatpickr("#fecha_hasta", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Confirmación antes de eliminar un pedido
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-ocultar alertas después de 5 segundos
            const alertas = document.querySelectorAll('.alert');
            if (alertas.length > 0) {
                setTimeout(function() {
                    alertas.forEach(alerta => {
                        alerta.style.opacity = '0';
                        alerta.style.transition = 'opacity 0.5s ease';
                        setTimeout(() => alerta.remove(), 500);
                    });
                }, 5000);
            }
        });
    </script>
</body>

</html>