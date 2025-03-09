<?php
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
        // Eliminar detalles del pedido primero
        $stmt = $conexion->prepare("DELETE FROM detalles_pedido WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        
        // Eliminar el pedido principal
        $stmt = $conexion->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        
        // Redirigir con mensaje de éxito
        header("Location: gestion_pedidos.php?mensaje=Pedido eliminado correctamente");
        exit();
    } catch (Exception $e) {
        header("Location: gestion_pedidos.php?mensaje=Error al eliminar el pedido: " . $e->getMessage());
        exit();
    }
}

// Consultar todos los pedidos con el nombre del usuario
$sel = $conexion->query("
    SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha_pedido DESC
");
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
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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
            
            .table td, .table th {
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
            <!-- Botón para ir a la sección de usuarios -->
            <a href="admin_home.php" class="btn-nav" title="Gestionar usuarios">
                <i class="fas fa-users"></i>Usuarios
            </a>
            <!-- Botón para ir a la sección de productos -->
            <a href="productos.php" class="btn-nav" title="Gestionar productos">
                <i class="fas fa-shopping-cart"></i>Productos
            </a>
            <!-- Botón para cerrar sesión -->
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
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>
        <?php endif; ?>
        
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
                        if ($sel->num_rows > 0) {
                            while ($fila = $sel->fetch_assoc()) {
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
    </div>

    <!-- Pie de página -->
    <footer>
        <p>© 2025 Sabor Colombiano - Gestión con raíces y tradición.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personalizado -->
    <script>
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
            
            // Confirmación de eliminación (ya implementada en el enlace con onclick)
        });
    </script>
</body>
</html>