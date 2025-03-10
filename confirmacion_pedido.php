<?php
// Iniciar sesión y verificar autenticación
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include('db.php');

// Verificar si hay un pedido en la sesión
if (!isset($_SESSION['ultimo_pedido']) && !isset($_SESSION['numero_pedido'])) {
    header("Location: productos_compra.php?mensaje=No hay pedido para confirmar&status=error");
    exit();
}

// Obtener el ID y número del pedido
$pedido_id = isset($_SESSION['ultimo_pedido']) ? $_SESSION['ultimo_pedido'] : null;
$numero_pedido = isset($_SESSION['numero_pedido']) ? $_SESSION['numero_pedido'] : "PED-" . $pedido_id;

// Obtener datos del pedido desde la base de datos
$stmt = $conexion->prepare("
    SELECT p.*, e.forma_pago, e.direccion, e.ciudad, e.telefono 
    FROM pedidos p 
    LEFT JOIN entregas e ON p.id = e.pedido_id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $fecha = $row['fecha_pedido'];
    $total = $row['total'];
    $estado = $row['estado'];
    $forma_pago = $row['forma_pago'];
    $direccion = $row['direccion'];
    $ciudad = $row['ciudad'];
    $telefono = $row['telefono'];
} else {
    // Si no se encuentra el pedido, redirigir
    header("Location: productos_compra.php?mensaje=Pedido no encontrado&status=error");
    exit();
}
$stmt->close();

// Obtener los productos del pedido - Consulta corregida
try {
    // Primero verificamos si la tabla productos tiene una columna imagen
    $check_column = $conexion->query("SHOW COLUMNS FROM productos LIKE 'imagen'");
    $has_imagen = $check_column->num_rows > 0;
    
    if ($has_imagen) {
        // Si existe la columna imagen, usamos la consulta original
        $stmt = $conexion->prepare("
            SELECT dp.*, p.nombre, p.imagen 
            FROM detalles_pedido dp 
            LEFT JOIN productos p ON dp.producto_id = p.id 
            WHERE dp.pedido_id = ?
        ");
    } else {
        // Si no existe la columna imagen, la excluimos de la consulta
        $stmt = $conexion->prepare("
            SELECT dp.*, p.nombre 
            FROM detalles_pedido dp 
            LEFT JOIN productos p ON dp.producto_id = p.id 
            WHERE dp.pedido_id = ?
        ");
    }
    
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    // Si hay algún error con la consulta, usamos una versión más simple
    $stmt = $conexion->prepare("
        SELECT * FROM detalles_pedido WHERE pedido_id = ?
    ");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

$cart = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $precio = $row['precio_unitario'];
    $cantidad = $row['cantidad'];
    $subtotalItem = $precio * $cantidad;
    $subtotal += $subtotalItem;
    
    $cart[] = [
        'id' => $row['producto_id'],
        'nombre' => $row['nombre'] ?? 'Producto #' . $row['producto_id'],
        'precio' => $precio,
        'cantidad' => $cantidad,
        'imagen' => $row['imagen'] ?? 'imagenes/producto_default.jpg'
    ];
}
$stmt->close();

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conexion->prepare("SELECT nombre, correo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $nombre_usuario = $row['nombre'];
    $email = $row['correo'];
} else {
    $nombre_usuario = $_SESSION['usuario'];
    $email = "usuario@ejemplo.com";
}
$stmt->close();

// Calcular impuestos y envío
$impuestos = $subtotal * 0.19;
$envio = 12000; // Valor fijo de envío
$total = $subtotal + $impuestos + $envio;

// Formatear método de pago para mostrar
$metodo_pago_texto = '';
switch ($forma_pago) {
    case 'tarjeta':
        $metodo_pago_texto = 'Tarjeta de Crédito/Débito';
        break;
    case 'transferencia':
        $metodo_pago_texto = 'Transferencia Bancaria';
        break;
    case 'efectivo':
        $metodo_pago_texto = 'Efectivo (contra entrega)';
        break;
    case 'nequi':
        $metodo_pago_texto = 'Nequi / Daviplata';
        break;
    default:
        $metodo_pago_texto = $forma_pago;
}

// Determinar tipo de envío (esto debería venir de la base de datos en un sistema real)
$tipo_envio = 'estandar'; // Por defecto
$tipo_envio_texto = $tipo_envio === 'express' ? 'Express (1-2 días)' : 'Estándar (3-5 días)';

// Calcular fecha estimada de entrega
$dias_entrega = $tipo_envio === 'express' ? 2 : 5;
$fecha_entrega = date('Y-m-d', strtotime($fecha . ' + ' . $dias_entrega . ' days'));
$fecha_entrega_formateada = date('d/m/Y', strtotime($fecha_entrega));

// Limpiar variables de sesión del pedido para evitar duplicados
// Comentado para permitir recargar la página
// unset($_SESSION['ultimo_pedido']);
// unset($_SESSION['numero_pedido']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Confirmación de pedido - San Basilio del Palenque">
    <title>Pedido Confirmado - San Basilio del Palenque</title>

    <!-- Preconexión a CDNs para mejorar rendimiento -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <!-- Bootstrap CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Montserrat y Poppins para una tipografía elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Animate.css para animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Estilos personalizados -->
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

        .btn-action.btn-outline {
            background-color: transparent;
            color: var(--color-primary);
            border: 2px solid var(--color-primary);
        }

        .btn-action.btn-outline:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
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

        /* Proceso de compra */
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-lg);
            position: relative;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--color-light-gray);
            z-index: 0;
        }

        .checkout-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--color-light-gray);
            color: var(--color-text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 700;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .step-label {
            font-size: 0.9rem;
            color: var(--color-text-light);
            text-align: center;
            transition: var(--transition);
        }

        .checkout-step.active .step-number {
            background-color: var(--color-primary);
            color: var(--color-light);
        }

        .checkout-step.active .step-label {
            color: var(--color-primary);
            font-weight: 600;
        }

        .checkout-step.completed .step-number {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }

        /* Tarjetas y contenedores */
        .confirmation-container {
            background-color: var(--color-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: var(--spacing-lg);
        }

        .confirmation-container:hover {
            box-shadow: var(--shadow-lg);
        }

        .confirmation-header {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .confirmation-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .confirmation-body {
            padding: var(--spacing-md);
        }

        .confirmation-footer {
            background-color: var(--color-light-gray);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Mensaje de éxito */
        .success-message {
            text-align: center;
            margin-bottom: var(--spacing-lg);
        }

        .success-icon {
            font-size: 5rem;
            color: var(--color-secondary);
            margin-bottom: var(--spacing-sm);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .success-title {
            font-size: 2rem;
            color: var(--color-secondary-dark);
            margin-bottom: var(--spacing-sm);
        }

        .success-subtitle {
            font-size: 1.2rem;
            color: var(--color-text);
            margin-bottom: var(--spacing-md);
        }

        /* Detalles del pedido */
        .order-details {
            margin-bottom: var(--spacing-lg);
        }

        .order-info {
            background-color: var(--color-light-gray);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .order-info-title {
            font-size: 1.2rem;
            color: var(--color-primary);
            margin-bottom: var(--spacing-sm);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            color: var(--color-text);
        }

        .info-value {
            color: var(--color-text);
        }

        /* Productos del pedido */
        .order-products {
            margin-bottom: var(--spacing-md);
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm);
            border-bottom: 1px solid var(--color-light-gray);
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius);
            object-fit: cover;
            margin-right: var(--spacing-sm);
        }

        .product-details {
            flex-grow: 1;
        }

        .product-name {
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.25rem;
        }

        .product-price {
            color: var(--color-text-light);
            font-size: 0.9rem;
        }

        .product-quantity {
            background-color: var(--color-light-gray);
            color: var(--color-text);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            margin-left: var(--spacing-sm);
        }

        /* Resumen de costos */
        .cost-summary {
            background-color: var(--color-light-gray);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .cost-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .cost-row:last-child {
            margin-bottom: 0;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .cost-label {
            color: var(--color-text);
        }

        .cost-value {
            font-weight: 600;
            color: var(--color-text);
        }

        .cost-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--color-primary-dark);
        }

        /* Instrucciones de pago */
        .payment-instructions {
            background-color: var(--color-light-gray);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .payment-instructions h4 {
            color: var(--color-primary);
            margin-bottom: var(--spacing-sm);
        }

        .payment-instructions p {
            margin-bottom: var(--spacing-sm);
        }

        .payment-instructions ul {
            padding-left: 1.5rem;
            margin-bottom: var(--spacing-sm);
        }

        .payment-instructions li {
            margin-bottom: 0.25rem;
        }

        /* Botones de acción */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .checkout-steps {
                overflow-x: auto;
                padding-bottom: var(--spacing-sm);
            }
            
            .checkout-step {
                min-width: 100px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: var(--spacing-md);
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .confirmation-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn-action {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .checkout-steps {
                flex-direction: column;
                gap: var(--spacing-sm);
                align-items: flex-start;
            }
            
            .checkout-steps::before {
                display: none;
            }
            
            .checkout-step {
                flex-direction: row;
                width: 100%;
                gap: var(--spacing-sm);
            }
            
            .step-number {
                margin-bottom: 0;
            }
            
            .info-row {
                flex-direction: column;
                margin-bottom: var(--spacing-sm);
            }
            
            .info-value {
                font-weight: 600;
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container main-content">
        <h1 class="page-title animate__animated animate__fadeIn">Confirmación de Pedido</h1>
        
        <!-- Pasos del proceso de compra -->
        <div class="checkout-steps animate__animated animate__fadeIn">
            <div class="checkout-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Carrito</div>
            </div>
            <div class="checkout-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Datos de envío</div>
            </div>
            <div class="checkout-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Pago</div>
            </div>
            <div class="checkout-step active">
                <div class="step-number">4</div>
                <div class="step-label">Confirmación</div>
            </div>
        </div>
        
        <!-- Mensaje de éxito -->
        <div class="success-message animate__animated animate__fadeIn">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="success-title">¡Pedido Confirmado!</h2>
            <p class="success-subtitle">Gracias por tu compra. Hemos recibido tu pedido correctamente.</p>
        </div>
        
        <!-- Detalles del pedido -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="confirmation-container animate__animated animate__fadeIn">
                    <div class="confirmation-header">
                        <h3><i class="fas fa-receipt me-2"></i> Detalles del Pedido</h3>
                        <span class="badge bg-light text-primary"><?php echo htmlspecialchars($numero_pedido); ?></span>
                    </div>
                    <div class="confirmation-body">
                        <!-- Información del pedido -->
                        <div class="order-info mb-4">
                            <h4 class="order-info-title">Información General</h4>
                            <div class="info-row">
                                <span class="info-label">Número de pedido:</span>
                                <span class="info-value"><?php echo htmlspecialchars($numero_pedido); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Fecha del pedido:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($fecha)); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Estado del pedido:</span>
                                <span class="info-value"><span class="badge bg-warning text-dark"><?php echo ucfirst($estado); ?></span></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Método de pago:</span>
                                <span class="info-value"><?php echo htmlspecialchars($metodo_pago_texto); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tipo de envío:</span>
                                <span class="info-value"><?php echo htmlspecialchars($tipo_envio_texto); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Fecha estimada de entrega:</span>
                                <span class="info-value"><?php echo htmlspecialchars($fecha_entrega_formateada); ?></span>
                            </div>
                        </div>
                        
                        <!-- Información de envío -->
                        <div class="order-info mb-4">
                            <h4 class="order-info-title">Información de Envío</h4>
                            <div class="info-row">
                                <span class="info-label">Nombre:</span>
                                <span class="info-value"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value"><?php echo htmlspecialchars($telefono); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value"><?php echo htmlspecialchars($direccion); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ciudad:</span>
                                <span class="info-value"><?php echo htmlspecialchars($ciudad); ?></span>
                            </div>
                        </div>
                        
                        <!-- Productos del pedido -->
                        <h4 class="order-info-title">Productos</h4>
                        <div class="order-products">
                            <?php foreach ($cart as $item): ?>
                                <div class="product-item">
                                    <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" class="product-image">
                                    <div class="product-details">
                                        <div class="product-name"><?php echo htmlspecialchars($item['nombre']); ?></div>
                                        <div class="product-price">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></div>
                                    </div>
                                    <div class="product-quantity">x<?php echo $item['cantidad']; ?></div>
                                    <div class="product-subtotal">$<?php echo number_format($item['precio'] * $item['cantidad'], 0, ',', '.'); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Resumen de costos -->
                        <div class="cost-summary mt-4">
                            <div class="cost-row">
                                <span class="cost-label">Subtotal:</span>
                                <span class="cost-value">$<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                            </div>
                            <div class="cost-row">
                                <span class="cost-label">IVA (19%):</span>
                                <span class="cost-value">$<?php echo number_format($impuestos, 0, ',', '.'); ?></span>
                            </div>
                            <div class="cost-row">
                                <span class="cost-label">Envío:</span>
                                <span class="cost-value">$<?php echo number_format($envio, 0, ',', '.'); ?></span>
                            </div>
                            <div class="cost-row">
                                <span class="cost-label cost-total">Total:</span>
                                <span class="cost-value cost-total">$<?php echo number_format($total, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Instrucciones según método de pago -->
                        <?php if ($forma_pago === 'transferencia'): ?>
                        <div class="payment-instructions mt-4">
                            <h4><i class="fas fa-info-circle me-2"></i> Instrucciones de Pago</h4>
                            <p>Por favor, realiza la transferencia a la siguiente cuenta bancaria:</p>
                            <ul>
                                <li><strong>Banco:</strong> Banco de Bogotá</li>
                                <li><strong>Tipo de cuenta:</strong> Ahorros</li>
                                <li><strong>Número:</strong> 123456789</li>
                                <li><strong>Titular:</strong> Fundación San Basilio del Palenque</li>
                                <li><strong>NIT:</strong> 900.123.456-7</li>
                            </ul>
                            <p>Una vez realizada la transferencia, envía el comprobante a <strong>pagos@sanbasilio.com</strong> indicando tu número de pedido.</p>
                        </div>
                        <?php elseif ($forma_pago === 'nequi'): ?>
                        <div class="payment-instructions mt-4">
                            <h4><i class="fas fa-info-circle me-2"></i> Instrucciones de Pago</h4>
                            <p>Por favor, realiza el pago a través de Nequi o Daviplata:</p>
                            <ul>
                                <li><strong>Nequi:</strong> 300 123 4567</li>
                                <li><strong>Daviplata:</strong> 310 987 6543</li>
                                <li><strong>Nombre:</strong> Fundación San Basilio del Palenque</li>
                            </ul>
                            <p>Una vez realizado el pago, envía el comprobante a <strong>pagos@sanbasilio.com</strong> indicando tu número de pedido.</p>
                        </div>
                        <?php elseif ($forma_pago === 'efectivo'): ?>
                        <div class="payment-instructions mt-4">
                            <h4><i class="fas fa-info-circle me-2"></i> Pago Contra Entrega</h4>
                            <p>Has seleccionado pago en efectivo contra entrega. Recuerda tener el monto exacto al momento de recibir tu pedido.</p>
                            <p>El repartidor te entregará una factura por tu compra.</p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Botones de acción -->
                        <div class="action-buttons">
                            <a href="productos_compra.php" class="btn-action btn-secondary">
                                <i class="fas fa-shopping-bag me-2"></i> Seguir Comprando
                            </a>
                            <a href="ver_pedidos.php" class="btn-action">
                                <i class="fas fa-list-alt me-2"></i> Ver Mis Pedidos
                            </a>
                            <a href="#" class="btn-action btn-outline" onclick="window.print()">
                                <i class="fas fa-print me-2"></i> Imprimir Comprobante
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mensaje adicional -->
                <div class="alert alert-info animate__animated animate__fadeIn">
                    <h5><i class="fas fa-envelope me-2"></i> Confirmación por Email</h5>
                    <p>Hemos enviado un correo electrónico a <strong><?php echo htmlspecialchars($email); ?></strong> con los detalles de tu pedido.</p>
                    <p>Si tienes alguna pregunta sobre tu pedido, no dudes en contactarnos a <strong>soporte@sanbasilio.com</strong> o llamar al <strong>(+57) 300 123 4567</strong>.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Limpiar el carrito en localStorage
            localStorage.removeItem('cart');
            
            // Mostrar confeti para celebrar la compra
            showConfetti();
        });
        
        // Función para mostrar confeti
        function showConfetti() {
            // Esta es una versión simple de confeti
            const colors = ['#FF5722', '#4CAF50', '#FFC107', '#2196F3', '#9C27B0'];
            const confettiCount = 200;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.zIndex = '1000';
                confetti.style.top = '-10px';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.opacity = Math.random() + 0.5;
                confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                
                document.body.appendChild(confetti);
                
                // Animación de caída
                const animation = confetti.animate(
                    [
                        { transform: 'translate3d(0, 0, 0)', opacity: 1 },
                        { transform: 'translate3d(' + (Math.random() * 100 - 50) + 'px, 100vh, 0)', opacity: 0 }
                    ],
                    {
                        duration: Math.random() * 3000 + 2000,
                        easing: 'cubic-bezier(0.1, 0.8, 0.3, 1)',
                        fill: 'forwards'
                    }
                );
                
                // Eliminar el elemento después de la animación
                animation.onfinish = () => {
                    confetti.remove();
                };
            }
        }
    </script>
</body>
</html>