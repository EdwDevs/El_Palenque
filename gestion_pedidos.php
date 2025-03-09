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
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración para gestionar pedidos de Sabor Colombiano">
    <title>Gestión de Pedidos - Sabor Colombiano</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <style>
        :root {
            --color-primary: #FF5722;
            --color-secondary: #4CAF50;
            --color-accent: #FFC107;
            --color-text: #333333;
            --color-light: #FFFFFF;
            --color-hover: #FFF3E0;
            --border-radius: 10px;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            --transition-normal: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: var(--color-text);
            padding-bottom: 60px;
            position: relative;
        }
        
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
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
        
        .header-logo img {
            max-width: 120px;
            border-radius: var(--border-radius);
            border: 3px solid var(--color-primary);
            transition: transform 0.3s ease;
        }
        
        .header-logo img:hover {
            transform: scale(1.05);
        }
        
        .user-welcome {
            color: var(--color-primary);
            font-weight: bold;
            font-size: 1.2rem;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            margin-right: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
        }
        
        .user-welcome i {
            margin-right: 0.5rem;
            color: var(--color-secondary);
        }
        
        .user-welcome:hover {
            transform: scale(1.05);
            background-color: rgba(255, 255, 255, 1);
        }
        
        .btn-salir, .btn-productos, .btn-usuarios {
            background-color: var(--color-primary);
            color: var(--color-light);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            margin-right: 1rem;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-salir:hover, .btn-productos:hover, .btn-usuarios:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .container {
            margin-top: 8rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            max-width: 1200px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        h1 {
            color: var(--color-primary);
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
            border-radius: 3px;
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2rem;
        }
        
        .table thead th {
            background-color: var(--color-secondary);
            color: var(--color-light);
            font-weight: 600;
            padding: 1rem;
            border: none;
            text-align: center;
            position: sticky;
            top: 0;
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
        
        .table tbody tr:hover {
            background-color: var(--color-hover);
            transform: scale(1.01);
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--color-accent);
            text-align: center;
            vertical-align: middle;
        }
        
        /* Estilo mejorado para la columna de usuario */
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
        }
        
        .status-cell {
            font-weight: 600;
        }
        
        .status-pendiente {
            color: var(--color-primary);
        }
        
        .status-confirmado {
            color: var(--color-secondary);
        }
        
        .status-enviado {
            color: var(--color-accent);
        }
        
        .status-entregado {
            color: var(--color-secondary);
        }
        
        .status-cancelado {
            color: #f44336;
        }
        
        .actions-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }
        
        .btn-editar {
            background-color: var(--color-accent);
            color: var(--color-text);
        }
        
        .btn-editar:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .btn-eliminar {
            background-color: #f44336;
            color: var(--color-light);
        }
        
        .btn-eliminar:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        footer {
            text-align: center;
            padding: 1rem;
            color: var(--color-text);
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.8);
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        @media (max-width: 768px) {
            .header-logo { margin-left: 1rem; }
            .header-logo img { max-width: 80px; }
            .user-welcome { font-size: 1rem; padding: 0.4rem 0.8rem; }
            .btn-salir, .btn-productos, .btn-usuarios { margin-right: 0.5rem; padding: 0.5rem 1rem; }
            .container { padding: 1.5rem; margin-top: 7rem; }
            h1 { font-size: 1.5rem; }
            .actions-cell { flex-direction: column; gap: 0.3rem; }
            .btn-action { width: 100%; justify-content: center; padding: 0.4rem 0.8rem; font-size: 0.9rem; }
            .table-responsive { overflow-x: auto; }
        }
        
        @media (max-width: 576px) {
            .container { padding: 1rem; margin-top: 6rem; }
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
        <div>
            <!-- Botón para ir a la sección de usuarios -->
            <a href="admin_home.php" title="Gestionar usuarios">
                <button class="btn-usuarios">
                    <i class="fas fa-users"></i>Usuarios
                </button>
            </a>
            <!-- Botón para ir a la sección de productos -->
            <a href="productos.php" title="Gestionar productos">
                <button class="btn-product HALLARos">
                    <i class="fas fa-shopping-cart"></i>Productos
                </button>
            </a>
            <!-- Botón para cerrar sesión -->
            <a href="logout.php" title="Cerrar sesión">
                <button class="btn-salir">
                    <i class="fas fa-sign-out-alt"></i>Salir
                </button>
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
                        <td><?php echo htmlspecialchars($fila['id']); ?></td>
                        <td class="user-cell">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($fila['usuario_nombre']); ?>
                        </td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($fila['fecha_pedido']))); ?></td>
                        <td>$<?php echo number_format($fila['total'], 2); ?></td>
                        <td class="status-cell <?php echo $estadoClass; ?>">
                            <?php echo ucfirst(htmlspecialchars($fila['estado'])); ?>
                        </td>
                        <td class="actions-cell">
                            <a href="ver_pedido.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-editar" title="Ver detalles del pedido">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="modificar_estado_pedido.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-editar" title="Modificar estado del pedido">
                                <i class="fas fa-edit"></i> Estado
                            </a>
                            <a href="gestion_pedidos.php?eliminar_pedido=<?php echo $fila['id']; ?>" class="btn-action btn-eliminar" title="Eliminar pedido" onclick="return confirm('¿Estás seguro de que deseas eliminar este pedido?')">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center">No hay pedidos registrados en el sistema.</td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
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
            const eliminarButtons = document.querySelectorAll('.btn-eliminar');
            
            eliminarButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este pedido? Esta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>