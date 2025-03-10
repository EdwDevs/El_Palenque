<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?redirect=ver_pedidos.php");
    exit();
}

include('db.php');

// Obtener el ID del usuario desde la sesión
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;
if (!$usuario_id) {
    $usuario = htmlspecialchars($_SESSION['usuario']);
    $stmt = $conexion->prepare("SELECT id, rol FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $usuario_id = $row['id'];
        $_SESSION['usuario_id'] = $usuario_id;
        if (!isset($_SESSION['rol'])) {
            $_SESSION['rol'] = $row['rol'];
        }
    } else {
        die("Usuario no encontrado en la base de datos.");
    }
    $stmt->close();
}

// Verificar si el usuario es administrador
$isAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

// Obtener los pedidos según el rol
if ($isAdmin) {
    // Si es admin, obtener todos los pedidos
    $stmt = $conexion->prepare("
        SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.fecha_pedido DESC
    ");
} else {
    // Si es usuario normal, obtener solo sus pedidos
    $stmt = $conexion->prepare("
        SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.usuario_id = ? 
        ORDER BY p.fecha_pedido DESC
    ");
    $stmt->bind_param("i", $usuario_id);
}

$stmt->execute();
$result_pedidos = $stmt->get_result();
$pedidos = [];
while ($row = $result_pedidos->fetch_assoc()) {
    $pedidos[] = $row;
}
$stmt->close();

// Calcular estadísticas
$total_pedidos = count($pedidos);
$pedidos_pendientes = 0;
$pedidos_confirmados = 0;
$total_gastado = 0;

foreach ($pedidos as $pedido) {
    if ($pedido['estado'] === 'pendiente') {
        $pedidos_pendientes++;
    } elseif ($pedido['estado'] === 'confirmado') {
        $pedidos_confirmados++;
    }
    $total_gastado += $pedido['total'];
}

// Procesar mensajes de notificación
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'success';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - San Basilio del Palenque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
    :root {
        --color-primary: #FF5722;
        --color-primary-light: #FF8A65;
        --color-primary-dark: #E64A19;
        --color-secondary: #4CAF50;
        --color-secondary-light: #81C784;
        --color-secondary-dark: #388E3C;
        --color-accent: #FFC107;
        --color-accent-light: #FFD54F;
        --color-text: #333333;
        --color-text-light: #757575;
        --color-light: #FFFFFF;
        --color-light-gray: #F5F5F5;
        --color-dark-gray: #424242;
        --color-danger: #f44336;
        --color-danger-dark: #d32f2f;
        --color-success: #4CAF50;
        --shadow-sm: 0 2px 5px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 8px 20px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s ease;
        --border-radius: 10px;
        --spacing-xs: 0.5rem;
        --spacing-sm: 1rem;
        --spacing-md: 1.5rem;
        --spacing-lg: 2rem;
        --spacing-xl: 3rem;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--color-light-gray);
        background-image: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 87, 34, 0.2), rgba(76, 175, 80, 0.2));
        background-attachment: fixed;
        color: var(--color-text);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    /* Header y Navegación */
    .custom-navbar {
        background: rgba(255, 255, 255, 0.95);
        padding: 0.5rem 1.5rem;
        box-shadow: var(--shadow-md);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        transition: var(--transition);
    }

    .navbar-brand img {
        max-width: 60px;
        border-radius: 50%;
        border: 2px solid var(--color-primary);
        transition: var(--transition);
    }

    .navbar-brand img:hover {
        transform: scale(1.05);
        border-color: var(--color-secondary);
    }

    .navbar-nav .nav-link {
        color: var(--color-text);
        font-weight: 600;
        padding: 0.5rem 1rem;
        transition: var(--transition);
        position: relative;
    }

    .navbar-nav .nav-link:hover {
        color: var(--color-primary);
    }

    .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: var(--color-primary);
        transition: var(--transition);
        transform: translateX(-50%);
    }

    .navbar-nav .nav-link:hover::after {
        width: 80%;
    }

    .user-welcome {
        color: var(--color-primary);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-welcome i {
        font-size: 1.2rem;
    }

    .btn-action {
        background-color: var(--color-primary);
        color: var(--color-light);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }

    .btn-action:hover {
        background-color: var(--color-primary-dark);
        color: var(--color-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-action.btn-secondary {
        background-color: var(--color-secondary);
    }

    .btn-action.btn-secondary:hover {
        background-color: var(--color-secondary-dark);
    }

    .btn-action.btn-danger {
        background-color: var(--color-danger);
    }

    .btn-action.btn-danger:hover {
        background-color: var(--color-danger-dark);
    }

    /* Contenido principal */
    .main-content {
        margin-top: 100px;
        padding: var(--spacing-lg);
        flex-grow: 1;
    }

    .page-title {
        color: var(--color-primary-dark);
        text-align: center;
        margin-bottom: var(--spacing-lg);
        position: relative;
        font-size: 2.2rem;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--color-primary), var(--color-secondary));
        border-radius: 2px;
    }

    /* Estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }

    .stat-card {
        background-color: var(--color-light);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        padding: var(--spacing-md);
        text-align: center;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-sm);
        color: var(--color-primary);
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: var(--color-text);
    }

    .stat-label {
        color: var(--color-text-light);
        font-size: 0.9rem;
    }

    /* Tabla de pedidos */
    .pedidos-container {
        background-color: var(--color-light);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        margin-bottom: var(--spacing-lg);
    }

    .pedidos-header {
        background-color: var(--color-primary-light);
        color: var(--color-light);
        padding: var(--spacing-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pedidos-header h2 {
        margin: 0;
        font-size: 1.5rem;
    }

    .pedidos-body {
        padding: var(--spacing-md);
    }

    .pedidos-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pedidos-table th {
        background-color: var(--color-light-gray);
        color: var(--color-text);
        font-weight: 600;
        text-align: left;
        padding: var(--spacing-sm);
        border-bottom: 2px solid var(--color-primary-light);
    }

    .pedidos-table td {
        padding: var(--spacing-sm);
        border-bottom: 1px solid var(--color-light-gray);
        vertical-align: middle;
    }

    .pedidos-table tr:hover {
        background-color: var(--color-light-gray);
    }

    .pedidos-table tr:last-child td {
        border-bottom: none;
    }

    .pedido-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .status-pendiente {
        background-color: var(--color-accent-light);
        color: var(--color-text);
    }

    .status-confirmado {
        background-color: var(--color-secondary-light);
        color: var(--color-text);
    }

    .status-cancelado {
        background-color: var(--color-danger);
        color: var(--color-light);
    }

    .btn-ver {
        background-color: var(--color-primary);
        color: var(--color-light);
        padding: 0.25rem 0.75rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .btn-ver:hover {
        background-color: var(--color-primary-dark);
        color: var(--color-light);
    }

    /* Mensaje sin pedidos */
    .no-pedidos {
        text-align: center;
        padding: var(--spacing-lg);
    }

    .no-pedidos-icon {
        font-size: 4rem;
        color: var(--color-text-light);
        margin-bottom: var(--spacing-md);
    }

    .no-pedidos-text {
        font-size: 1.2rem;
        color: var(--color-text);
        margin-bottom: var(--spacing-md);
    }

    /* Notificaciones */
    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
    }

    .custom-toast {
        background-color: var(--color-light);
        color: var(--color-text);
        border-radius: var(--border-radius);
        padding: 1rem;
        margin-bottom: 0.5rem;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 1rem;
        max-width: 350px;
        animation: slideInRight 0.3s forwards;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--color-light);
        font-size: 1.2rem;
    }

    .toast-icon.success {
        background-color: var(--color-secondary);
    }

    .toast-icon.error {
        background-color: var(--color-danger);
    }

    .toast-content {
        flex-grow: 1;
    }

    .toast-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .toast-message {
        font-size: 0.9rem;
        margin: 0;
    }

    .toast-close {
        background: none;
        border: none;
        color: var(--color-text-light);
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0;
    }

    /* Footer */
    footer {
        background-color: var(--color-dark-gray);
        color: var(--color-light);
        padding: var(--spacing-md);
        text-align: center;
        margin-top: auto;
    }

    footer p {
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: var(--spacing-md);
        }

        .page-title {
            font-size: 1.8rem;
        }

        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }

        .pedidos-header {
            flex-direction: column;
            gap: var(--spacing-sm);
            align-items: flex-start;
        }

        .pedidos-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 576px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="imagenes/logo.jpeg" alt="San Basilio de Palenque" width="60" height="60">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="productos_compra.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">Carrito</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="user-welcome d-none d-md-flex">
                        <i class="fas fa-user-circle"></i> Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                    </span>

                    <?php if ($isAdmin): ?>
                    <a href="admin_home.php" class="btn-action btn-secondary">
                        <i class="fas fa-cogs"></i> Panel Admin
                    </a>
                    <?php endif; ?>

                    <a href="logout.php" class="btn-action btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container main-content">
        <h1 class="page-title animate__animated animate__fadeIn">
            <?php echo $isAdmin ? 'Todos los Pedidos' : 'Mis Pedidos'; ?>
        </h1>

        <!-- Estadísticas -->
        <div class="stats-container animate__animated animate__fadeIn">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo $total_pedidos; ?></div>
                <div class="stat-label">Total de Pedidos</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $pedidos_pendientes; ?></div>
                <div class="stat-label">Pedidos Pendientes</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $pedidos_confirmados; ?></div>
                <div class="stat-label">Pedidos Confirmados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($total_gastado, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Gastado</div>
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <div class="pedidos-container animate__animated animate__fadeIn">
            <div class="pedidos-header">
                <h2><i class="fas fa-list-alt me-2"></i> <?php echo $isAdmin ? 'Listado de Pedidos' : 'Tus Pedidos'; ?>
                </h2>
                <?php if ($isAdmin): ?>
                <span class="badge bg-light text-primary"><?php echo $total_pedidos; ?> pedidos en total</span>
                <?php endif; ?>
            </div>

            <div class="pedidos-body">
                <?php if (count($pedidos) > 0): ?>
                <div class="table-responsive">
                    <table class="pedidos-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <?php if ($isAdmin): ?>
                                <th>Usuario</th>
                                <?php endif; ?>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td>#<?php echo $pedido['id']; ?></td>
                                <?php if ($isAdmin): ?>
                                <td><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></td>
                                <?php endif; ?>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="pedido-status status-<?php echo $pedido['estado']; ?>">
                                        <?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-pedidos">
                    <div class="no-pedidos-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="no-pedidos-text">
                        No tienes pedidos realizados aún.
                    </div>
                    <a href="productos_compra.php" class="btn-action">
                        <i class="fas fa-shopping-cart"></i> Ir a Comprar
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Contenedor de notificaciones Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Footer -->
    <footer>
        <p>© 2025 San Basilio del Palenque - Todos los derechos reservados</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar si hay mensajes de la URL
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('mensaje');
        const status = urlParams.get('status');

        if (message) {
            showToast(message, status || 'success');
        }
    });

    // Función para mostrar notificaciones toast
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');

        // Crear elemento toast
        const toast = document.createElement('div');
        toast.className = 'custom-toast animate__animated animate__fadeInRight';

        // Contenido del toast
        toast.innerHTML = `
                <div class="toast-icon ${type}">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                </div>
                <div class="toast-content">
                    <h5 class="toast-title">${type === 'success' ? 'Éxito' : 'Atención'}</h5>
                    <p class="toast-message">${message}</p>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            `;

        // Agregar al contenedor
        toastContainer.appendChild(toast);

        // Auto-eliminar después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('animate__fadeInRight');
            toast.classList.add('animate__fadeOutRight');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    </script>
</body>

</html>
<?php $conexion->close(); ?>