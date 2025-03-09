<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?redirect=ver_pedido.php");
    exit();
}

include('db.php');
require('fpdf/fpdf.php'); // Incluir FPDF

// Obtener el ID del usuario desde la sesión
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;
if (!$usuario_id) {
    $usuario = htmlspecialchars($_SESSION['usuario']);
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $usuario_id = $row['id'];
        $_SESSION['usuario_id'] = $usuario_id;
    } else {
        die("Usuario no encontrado en la base de datos.");
    }
    $stmt->close();
}

// Verificar si se proporcionó un ID de pedido
$isAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ver_pedidos.php?mensaje=Por favor selecciona un pedido específico&status=error");
    exit();
}

$pedido_id = intval($_GET['id']);

// Obtener el pedido específico
if ($isAdmin) {
    $stmt = $conexion->prepare("
        SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $pedido_id);
} else {
    $stmt = $conexion->prepare("
        SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = ? AND p.usuario_id = ?
    ");
    $stmt->bind_param("ii", $pedido_id, $usuario_id);
}
$stmt->execute();
$result_pedido = $stmt->get_result();
$pedido = $result_pedido->fetch_assoc();
$stmt->close();

if (!$pedido) {
    header("Location: ver_pedidos.php?mensaje=Pedido no encontrado o no tienes permiso&status=error");
    exit();
}

// Obtener detalles del pedido
$stmt = $conexion->prepare("
    SELECT dp.producto_id, dp.cantidad, dp.precio_unitario, p.nombre, p.imagen 
    FROM detalles_pedido dp 
    LEFT JOIN productos p ON dp.producto_id = p.id 
    WHERE dp.pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result_detalles = $stmt->get_result();
$detalles = [];
while ($row = $result_detalles->fetch_assoc()) {
    $detalles[] = $row;
}
$stmt->close();

// Obtener datos de entrega
$stmt = $conexion->prepare("
    SELECT forma_pago, direccion, ciudad, telefono 
    FROM entregas 
    WHERE pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result_entrega = $stmt->get_result();
$entrega = $result_entrega->fetch_assoc();
$stmt->close();

// Generar PDF y actualizar estado si se solicita facturar (solo para admins)
if ($isAdmin && isset($_GET['facturar']) && $_GET['facturar'] == '1') {
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'confirmado' WHERE id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $stmt->close();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Factura - San Basilio del Palenque', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Detalles del Pedido #{$pedido['id']}", 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Cliente: {$pedido['usuario_nombre']}", 0, 1);
    $pdf->Cell(0, 10, "Fecha del Pedido: " . date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])), 0, 1);
    $pdf->Cell(0, 10, "Estado: Confirmado", 0, 1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Datos de Entrega", 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Forma de Pago: " . ucfirst($entrega['forma_pago'] ?? 'No especificado'), 0, 1);
    $pdf->Cell(0, 10, "Dirección: " . ($entrega['direccion'] ?? 'No especificado'), 0, 1);
    $pdf->Cell(0, 10, "Ciudad: " . ($entrega['ciudad'] ?? 'No especificado'), 0, 1);
    $pdf->Cell(0, 10, "Teléfono: " . ($entrega['telefono'] ?? 'No especificado'), 0, 1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, 'Producto', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(40, 10, 'Precio Unitario', 1);
    $pdf->Cell(40, 10, 'Subtotal', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($detalles as $detalle) {
        $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
        $pdf->Cell(80, 10, $detalle['nombre'] ?: "Producto ID: {$detalle['producto_id']}", 1);
        $pdf->Cell(30, 10, $detalle['cantidad'], 1, 0, 'C');
        $pdf->Cell(40, 10, '$' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(40, 10, '$' . number_format($subtotal, 0, ',', '.'), 1, 0, 'R');
        $pdf->Ln();
    }

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, 'Total:', 0);
    $pdf->Cell(40, 10, '$' . number_format($pedido['total'], 0, ',', '.'), 0, 1, 'R');

    $pdf->Output('D', "factura_pedido_{$pedido_id}.pdf");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido #<?php echo $pedido_id; ?> - San Basilio del Palenque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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

        h1, h2, h3, h4, h5, h6 {
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

        /* Detalles del pedido */
        .pedido-container {
            background-color: var(--color-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: var(--spacing-lg);
        }

        .pedido-header {
            background-color: var(--color-primary-light);
            color: var(--color-light);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pedido-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .pedido-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            background-color: var(--color-light);
        }

        .status-pendiente {
            color: var(--color-accent);
        }

        .status-confirmado {
            color: var(--color-secondary);
        }

        .status-cancelado {
            color: var(--color-danger);
        }

        .pedido-body {
            padding: var(--spacing-md);
        }

        .pedido-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .info-group {
            margin-bottom: var(--spacing-sm);
        }

        .info-label {
            font-weight: 600;
            color: var(--color-text-light);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 600;
            color: var(--color-text);
            font-size: 1.1rem;
        }

        .pedido-section {
            margin-bottom: var(--spacing-md);
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--color-primary);
            margin-bottom: var(--spacing-sm);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-light-gray);
        }

        /* Tabla de productos */
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: var(--spacing-md);
        }

        .productos-table th {
            background-color: var(--color-light-gray);
            color: var(--color-text);
            font-weight: 600;
            text-align: left;
            padding: var(--spacing-sm);
            border-bottom: 2px solid var(--color-primary-light);
        }

        .productos-table td {
            padding: var(--spacing-sm);
            border-bottom: 1px solid var(--color-light-gray);
            vertical-align: middle;
        }

        .productos-table tr:hover {
            background-color: var(--color-light-gray);
        }

        .productos-table tr:last-child td {
            border-bottom: none;
        }

        .producto-imagen {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .producto-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Resumen del pedido */
        .pedido-resumen {
            background-color: var(--color-light-gray);
            padding: var(--spacing-md);
            border-radius: var(--border-radius);
            margin-top: var(--spacing-md);
        }

        .resumen-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .resumen-label {
            font-weight: 600;
            color: var(--color-text);
        }

        .resumen-value {
            font-weight: 700;
            color: var(--color-text);
        }

        .resumen-total {
            font-size: 1.2rem;
            color: var(--color-primary-dark);
            border-top: 2px solid var(--color-primary-light);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }

        /* Acciones del pedido */
        .pedido-actions {
            display: flex;
            justify-content: space-between;
            margin-top: var(--spacing-md);
            flex-wrap: wrap;
            gap: var(--spacing-sm);
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
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
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
            
            .pedido-header {
                flex-direction: column;
                gap: var(--spacing-sm);
                align-items: flex-start;
            }
            
            .pedido-status {
                align-self: flex-start;
            }
            
            .productos-table {
                display: block;
                overflow-x: auto;
            }
            
            .pedido-actions {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="60" height="60">
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
                        <a class="nav-link" href="tradiciones.php">Tradiciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="productos_compra.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="historias.php">Historias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacto.php">Contacto</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <span class="user-welcome d-none d-md-flex">
                        <i class="fas fa-user-circle"></i> Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                    </span>
                    
                    <a href="ver_pedidos.php" class="btn-action">
                        <i class="fas fa-list"></i> Mis Pedidos
                    </a>
                    
                    <?php if ($isAdmin): ?>
                    <a href="admin_panel.php" class="btn-action btn-secondary">
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
            Detalles del Pedido #<?php echo htmlspecialchars($pedido['id']); ?>
        </h1>
        
        <div class="pedido-container animate__animated animate__fadeIn">
            <div class="pedido-header">
                <h2>Pedido #<?php echo htmlspecialchars($pedido['id']); ?></h2>
                <span class="pedido-status status-<?php echo $pedido['estado']; ?>">
                    <?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?>
                </span>
            </div>
            
            <div class="pedido-body">
                <div class="pedido-info">
                    <div>
                        <div class="info-group">
                            <div class="info-label">Cliente</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Fecha del Pedido</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="info-group">
                            <div class="info-label">Total</div>
                            <div class="info-value">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Estado</div>
                            <div class="info-value"><?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($entrega): ?>
                <div class="pedido-section">
                    <h3 class="section-title">Datos de Entrega</h3>
                    <div class="pedido-info">
                        <div>
                            <div class="info-group">
                                <div class="info-label">Forma de Pago</div>
                                <div class="info-value"><?php echo ucfirst(htmlspecialchars($entrega['forma_pago'] ?? 'No especificado')); ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Dirección</div>
                                <div class="info-value"><?php echo htmlspecialchars($entrega['direccion'] ?? 'No especificado'); ?></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="info-group">
                                <div class="info-label">Ciudad</div>
                                <div class="info-value"><?php echo htmlspecialchars($entrega['ciudad'] ?? 'No especificado'); ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Teléfono</div>
                                <div class="info-value"><?php echo htmlspecialchars($entrega['telefono'] ?? 'No especificado'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="pedido-section">
                    <h3 class="section-title">Productos</h3>
                    <div class="table-responsive">
                        <table class="productos-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio Unitario</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($detalles as $detalle): 
                                    $itemSubtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
                                    $subtotal += $itemSubtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (isset($detalle['imagen']) && $detalle['imagen']): ?>
                                            <div class="producto-imagen">
                                                <img src="<?php echo htmlspecialchars($detalle['imagen']); ?>" alt="<?php echo htmlspecialchars($detalle['nombre'] ?: 'Producto'); ?>">
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <?php if ($detalle['nombre']): ?>
                                                    <?php echo htmlspecialchars($detalle['nombre']); ?>
                                                <?php else: ?>
                                                    Producto ID: <?php echo $detalle['producto_id']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?></td>
                                    <td><?php echo $detalle['cantidad']; ?></td>
                                    <td>$<?php echo number_format($itemSubtotal, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                        </table>
                    </div>
                    
                    <div class="pedido-resumen">
                        <div class="resumen-row">
                            <span class="resumen-label">Subtotal:</span>
                            <span class="resumen-value">$<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                        </div>
                        <div class="resumen-row">
                            <span class="resumen-label">IVA (19%):</span>
                            <span class="resumen-value">$<?php echo number_format($pedido['total'] - $subtotal, 0, ',', '.'); ?></span>
                        </div>
                        <div class="resumen-row resumen-total">
                            <span class="resumen-label">Total:</span>
                            <span class="resumen-value">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="pedido-actions">
                    <a href="ver_pedidos.php" class="btn-action">
                        <i class="fas fa-arrow-left"></i> Volver a Mis Pedidos
                    </a>
                    
                    <?php if ($pedido['estado'] !== 'confirmado'): ?>
                        <?php if ($isAdmin): ?>
                            <a href="ver_pedido.php?id=<?php echo $pedido_id; ?>&facturar=1" class="btn-action btn-secondary">
                                <i class="fas fa-file-pdf"></i> Generar Factura
                            </a>
                        <?php else: ?>
                            <button class="btn-action" disabled style="background-color: #ccc; cursor: not-allowed;">
                                <i class="fas fa-file-pdf"></i> Factura (Solo Admins)
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="ver_pedido.php?id=<?php echo $pedido_id; ?>&facturar=1" class="btn-action btn-secondary">
                            <i class="fas fa-file-pdf"></i> Descargar Factura
                        </a>
                    <?php endif; ?>
                </div>
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