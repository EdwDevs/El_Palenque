<?php
// Iniciar sesión y verificar autenticación
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?redirect=carrito.php");
    exit();
}

// Verificar si hay datos del carrito
if (!isset($_POST['cart']) || empty($_POST['cart'])) {
    // Si no hay carrito en POST, verificar si hay en la sesión
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
    } else {
        // No hay carrito ni en POST ni en sesión, redirigir
        header("Location: carrito.php?message=No hay productos en el carrito&status=error");
        exit();
    }
} else {
    // Decodificar el carrito desde POST
    $cart = json_decode($_POST['cart'], true);
    if (empty($cart)) {
        header("Location: carrito.php?message=El carrito está vacío&status=error");
        exit();
    }
}

// Guardar el carrito en la sesión para asegurar que esté disponible
$_SESSION['cart'] = $cart;

// Obtener el total del carrito
$subtotal = 0;
foreach ($cart as $item) {
    $precio = floatval($item['precio'] ?? $item['price'] ?? 0);
    $cantidad = intval($item['cantidad'] ?? 1);
    $subtotal += $precio * $cantidad;
}

// Calcular impuestos y total
$impuestos = $subtotal * 0.19; // 19% IVA
$total = $subtotal + $impuestos;
$envio = 15000; // Costo fijo de envío
$granTotal = $total + $envio;

// Obtener departamentos de Colombia para el formulario
$departamentos = [
    'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 'Caldas', 
    'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 
    'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena', 'Meta', 'Nariño', 
    'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda', 'San Andrés y Providencia', 
    'Santander', 'Sucre', 'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada'
];

// Inicializar datos del usuario (sin necesidad de conexión a base de datos)
$usuario = [
    'nombre' => $_SESSION['usuario'] ?? '',
    'apellido' => $_SESSION['apellido'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'telefono' => $_SESSION['telefono'] ?? '',
    'direccion' => $_SESSION['direccion'] ?? '',
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Finaliza tu compra de productos de San Basilio del Palenque">
    <title>Finalizar Compra - San Basilio del Palenque</title>

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
        .checkout-container {
            background-color: var(--color-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: var(--spacing-lg);
        }

        .checkout-container:hover {
            box-shadow: var(--shadow-lg);
        }

        .checkout-header {
            background-color: var(--color-primary-light);
            color: var(--color-light);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkout-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .checkout-body {
            padding: var(--spacing-md);
        }

        .checkout-footer {
            background-color: var(--color-light-gray);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Resumen del pedido */
        .order-summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-summary-table th {
            background-color: var(--color-light-gray);
            color: var(--color-text);
            font-weight: 600;
            padding: 0.75rem;
            text-align: left;
        }

        .order-summary-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--color-light-gray);
        }

        .order-summary-table tr:last-child td {
            border-bottom: none;
        }

        .order-summary-table .product-image {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius);
            object-fit: cover;
        }

        .order-summary-table .product-name {
            font-weight: 600;
            color: var(--color-text);
        }

        .order-summary-table .product-price {
            color: var(--color-secondary-dark);
            font-weight: 600;
        }

        .order-summary-table .product-quantity {
            text-align: center;
            font-weight: 600;
        }

        .order-summary-table .product-subtotal {
            color: var(--color-primary);
            font-weight: 700;
            text-align: right;
        }

        .order-summary-table .order-total {
            font-size: 1.2rem;
            color: var(--color-primary-dark);
            font-weight: 700;
        }

        /* Formulario de datos */
        .form-section {
            margin-bottom: var(--spacing-md);
        }

        .form-section-title {
            font-size: 1.2rem;
            color: var(--color-primary);
            margin-bottom: var(--spacing-sm);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--color-light-gray);
        }

        .form-floating > label {
            color: var(--color-text-light);
        }

        .form-control:focus {
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 0.25rem rgba(255, 87, 34, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        /* Métodos de pago */
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-md);
        }

        .payment-method {
            flex: 1;
            min-width: 120px;
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .payment-method label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
            background-color: var(--color-light);
            border: 2px solid var(--color-light-gray);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            height: 100%;
        }

        .payment-method input[type="radio"]:checked + label {
            border-color: var(--color-primary);
            background-color: rgba(255, 87, 34, 0.05);
        }

        .payment-method label i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--color-text-light);
            transition: var(--transition);
        }

        .payment-method input[type="radio"]:checked + label i {
            color: var(--color-primary);
        }

        .payment-method-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .payment-method-description {
            font-size: 0.8rem;
            color: var(--color-text-light);
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
            
            .checkout-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .payment-methods {
                flex-direction: column;
            }
            
            .payment-method {
                width: 100%;
            }
            
            .payment-method label {
                flex-direction: row;
                justify-content: flex-start;
                gap: 1rem;
                text-align: left;
            }
            
            .payment-method label i {
                margin-bottom: 0;
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
            
            .order-summary-table .product-image {
                display: none;
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
                    
                    <a href="carrito.php" class="btn-action btn-outline">
                        <i class="fas fa-arrow-left"></i> Volver al carrito
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container main-content">
        <h1 class="page-title animate__animated animate__fadeIn">Finalizar Compra</h1>
        
        <!-- Pasos del proceso de compra -->
        <div class="checkout-steps animate__animated animate__fadeIn">
            <div class="checkout-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Carrito</div>
            </div>
            <div class="checkout-step active">
                <div class="step-number">2</div>
                <div class="step-label">Datos de envío</div>
            </div>
            <div class="checkout-step">
                <div class="step-number">3</div>
                <div class="step-label">Pago</div>
            </div>
            <div class="checkout-step">
                <div class="step-number">4</div>
                <div class="step-label">Confirmación</div>
            </div>
        </div>
        
        <!-- Formulario de checkout -->
        <form action="procesar_pedido.php" method="post" id="checkoutForm">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Resumen del pedido -->
                    <div class="checkout-container animate__animated animate__fadeIn">
                        <div class="checkout-header">
                            <h3><i class="fas fa-shopping-basket me-2"></i> Resumen del Pedido</h3>
                            <span class="badge bg-light text-primary"><?php echo count($cart); ?> productos</span>
                        </div>
                        <div class="checkout-body">
                            <div class="table-responsive">
                                <table class="order-summary-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px"></th>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cant.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart as $item): ?>
                                            <?php 
                                                $nombre = $item['nombre'] ?? $item['name'] ?? "Producto ID: {$item['id']}";
                                                $precio = floatval($item['precio'] ?? $item['price'] ?? 0);
                                                $cantidad = intval($item['cantidad'] ?? 1);
                                                $subtotalItem = $precio * $cantidad;
                                                $imagen = $item['imagen'] ?? 'imagenes/producto_default.jpg';
                                            ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars($nombre); ?>" class="product-image">
                                                </td>
                                                <td class="product-name"><?php echo htmlspecialchars($nombre); ?></td>
                                                <td class="product-price">$<?php echo number_format($precio, 0, ',', '.'); ?></td>
                                                <td class="product-quantity"><?php echo $cantidad; ?></td>
                                                <td class="product-subtotal">$<?php echo number_format($subtotalItem, 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
                                    <span class="cost-label cost-total">Total a pagar:</span>
                                    <span class="cost-value cost-total">$<?php echo number_format($granTotal, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Datos de envío -->
                    <div class="checkout-container animate__animated animate__fadeIn">
                        <div class="checkout-header">
                            <h3><i class="fas fa-truck me-2"></i> Datos de Envío</h3>
                        </div>
                        <div class="checkout-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                                        <label for="nombre">Nombre</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" required>
                                        <label for="apellido">Apellido</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" required>
                                        <label for="telefono">Teléfono</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección" value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>" required>
                                        <label for="direccion">Dirección completa</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-floating mb-3">
                                        <select class="form-select" id="departamento" name="departamento" required>
                                            <option value="" selected disabled>Seleccione...</option>
                                            <?php foreach ($departamentos as $departamento): ?>
                                                <option value="<?php echo htmlspecialchars($departamento); ?>"><?php echo htmlspecialchars($departamento); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="departamento">Departamento</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" required>
                                        <label for="ciudad">Ciudad</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" placeholder="Código Postal">
                                        <label for="codigo_postal">Código Postal (opcional)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="tipo_envio" name="tipo_envio" required>
                                            <option value="estandar" selected>Estándar (3-5 días)</option>
                                            <option value="express">Express (1-2 días) +$10.000</option>
                                        </select>
                                        <label for="tipo_envio">Tipo de Envío</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="guardar_direccion" name="guardar_direccion" value="1" checked>
                                <label class="form-check-label" for="guardar_direccion">
                                    Guardar esta dirección para futuras compras
                                </label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="notas" name="notas" style="height: 100px" placeholder="Notas adicionales"></textarea>
                                <label for="notas">Notas adicionales (opcional)</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Método de pago -->
                    <div class="checkout-container animate__animated animate__fadeIn">
                        <div class="checkout-header">
                            <h3><i class="fas fa-credit-card me-2"></i> Método de Pago</h3>
                        </div>
                        <div class="checkout-body">
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" name="metodo_pago" id="pago_tarjeta" value="tarjeta" required>
                                    <label for="pago_tarjeta">
                                        <i class="fas fa-credit-card"></i>
                                        <div class="payment-method-title">Tarjeta de Crédito/Débito</div>
                                        <div class="payment-method-description">Pago seguro con tarjeta</div>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" name="metodo_pago" id="pago_transferencia" value="transferencia">
                                    <label for="pago_transferencia">
                                        <i class="fas fa-university"></i>
                                        <div class="payment-method-title">Transferencia Bancaria</div>
                                        <div class="payment-method-description">Pago mediante transferencia</div>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" name="metodo_pago" id="pago_efectivo" value="efectivo">
                                    <label for="pago_efectivo">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div class="payment-method-title">Efectivo</div>
                                        <div class="payment-method-description">Pago contra entrega</div>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" name="metodo_pago" id="pago_nequi" value="nequi">
                                    <label for="pago_nequi">
                                        <i class="fas fa-mobile-alt"></i>
                                        <div class="payment-method-title">Nequi / Daviplata</div>
                                        <div class="payment-method-description">Pago con billetera móvil</div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Detalles de pago con tarjeta (se muestra/oculta según selección) -->
                            <div id="tarjeta_detalles" class="payment-details mt-3" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="tarjeta_nombre" name="tarjeta_nombre" placeholder="Nombre en la tarjeta">
                                            <label for="tarjeta_nombre">Nombre en la tarjeta</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="tarjeta_numero" name="tarjeta_numero" placeholder="Número de tarjeta">
                                            <label for="tarjeta_numero">Número de tarjeta</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="tarjeta_expiracion" name="tarjeta_expiracion" placeholder="MM/AA">
                                            <label for="tarjeta_expiracion">Fecha de expiración (MM/AA)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="tarjeta_cvv" name="tarjeta_cvv" placeholder="CVV">
                                            <label for="tarjeta_cvv">Código de seguridad (CVV)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detalles de transferencia bancaria -->
                            <div id="transferencia_detalles" class="payment-details mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle me-2"></i> Datos para transferencia</h5>
                                    <p>Realiza tu transferencia a la siguiente cuenta:</p>
                                    <ul>
                                        <li><strong>Banco:</strong> Banco de Bogotá</li>
                                        <li><strong>Tipo de cuenta:</strong> Ahorros</li>
                                        <li><strong>Número:</strong> 123456789</li>
                                        <li><strong>Titular:</strong> Fundación San Basilio del Palenque</li>
                                        <li><strong>NIT:</strong> 900.123.456-7</li>
                                    </ul>
                                    <p>Una vez realizada la transferencia, envía el comprobante a <strong>pagos@sanbasilio.com</strong> indicando tu número de pedido.</p>
                                </div>
                            </div>
                            
                            <!-- Detalles de pago con Nequi/Daviplata -->
                            <div id="nequi_detalles" class="payment-details mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle me-2"></i> Datos para pago móvil</h5>
                                    <p>Realiza tu pago a los siguientes números:</p>
                                    <ul>
                                        <li><strong>Nequi:</strong> 300 123 4567</li>
                                        <li><strong>Daviplata:</strong> 310 987 6543</li>
                                        <li><strong>Nombre:</strong> Fundación San Basilio del Palenque</li>
                                    </ul>
                                    <p>Una vez realizado el pago, envía el comprobante a <strong>pagos@sanbasilio.com</strong> indicando tu número de pedido.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen de la orden (columna lateral) -->
                <div class="col-lg-4">
                    <div class="checkout-container animate__animated animate__fadeIn">
                        <div class="checkout-header">
                            <h3><i class="fas fa-receipt me-2"></i> Tu Pedido</h3>
                        </div>
                        <div class="checkout-body">
                            <div class="cost-summary">
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
                                    <span class="cost-value" id="costo_envio">$<?php echo number_format($envio, 0, ',', '.'); ?></span>
                                </div>
                                <div class="cost-row">
                                    <span class="cost-label cost-total">Total:</span>
                                    <span class="cost-value cost-total" id="gran_total">$<?php echo number_format($granTotal, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3 mt-4">
                                <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required>
                                <label class="form-check-label" for="terminos">
                                    He leído y acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#terminosModal">términos y condiciones</a>
                                </label>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="politica_privacidad" name="politica_privacidad" required>
                                <label class="form-check-label" for="politica_privacidad">
                                    Acepto la <a href="#" data-bs-toggle="modal" data-bs-target="#privacidadModal">política de privacidad</a>
                                </label>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="d-grid gap-2">
                            <button type="submit" class="btn-action btn-secondary btn-lg" id="btnConfirmarPedido">
    <i class="fas fa-check-circle me-2"></i> Confirmar Pedido
</button>
                                <a href="carrito.php" class="btn-action btn-outline">
                                    <i class="fas fa-arrow-left me-2"></i> Volver al Carrito
                                </a>
                            </div>
                            
                            <!-- Información de seguridad -->
                            <div class="text-center mt-4">
                                <p class="small text-muted">
                                    <i class="fas fa-lock me-1"></i> Pago 100% seguro
                                </p>
                                <div class="d-flex justify-content-center gap-2">
                                    <i class="fab fa-cc-visa fa-2x text-muted"></i>
                                    <i class="fab fa-cc-mastercard fa-2x text-muted"></i>
                                    <i class="fab fa-cc-amex fa-2x text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Campos ocultos para enviar datos -->
    <input type="hidden" name="cart" value='<?php echo htmlspecialchars(json_encode($cart), ENT_QUOTES, 'UTF-8'); ?>'>
    <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
    <input type="hidden" name="impuestos" value="<?php echo $impuestos; ?>">
    <input type="hidden" name="envio" value="<?php echo $envio; ?>">
    <input type="hidden" name="total" value="<?php echo $granTotal; ?>" id="total_hidden">
</form>
    </main>

    <!-- Modal de Términos y Condiciones -->
    <div class="modal fade" id="terminosModal" tabindex="-1" aria-labelledby="terminosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="terminosModalLabel">Términos y Condiciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Términos y Condiciones de Compra</h4>
                    <p>Bienvenido a la tienda en línea de San Basilio del Palenque. Al utilizar nuestro servicio, aceptas los siguientes términos y condiciones:</p>
                    
                    <h5>1. Productos y Precios</h5>
                    <p>Todos los productos están sujetos a disponibilidad. Los precios están expresados en pesos colombianos e incluyen IVA. Nos reservamos el derecho de modificar los precios en cualquier momento sin previo aviso.</p>
                    
                    <h5>2. Pedidos</h5>
                    <p>Al realizar un pedido, recibirás una confirmación por correo electrónico. Esto no implica la aceptación del pedido, ya que está sujeto a verificación de disponibilidad y datos de pago.</p>
                    
                    <h5>3. Envíos</h5>
                    <p>Los tiempos de entrega son estimados y pueden variar según la ubicación geográfica. No nos hacemos responsables por retrasos causados por terceros o situaciones de fuerza mayor.</p>
                    
                    <h5>4. Devoluciones</h5>
                    <p>Tienes derecho a devolver tu compra dentro de los 5 días siguientes a la recepción del producto, siempre que esté en perfecto estado y con su embalaje original.</p>
                    
                    <h5>5. Protección de Datos</h5>
                    <p>La información proporcionada será tratada conforme a nuestra política de privacidad y la legislación vigente en materia de protección de datos.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Política de Privacidad -->
    <div class="modal fade" id="privacidadModal" tabindex="-1" aria-labelledby="privacidadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacidadModalLabel">Política de Privacidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Política de Privacidad</h4>
                    <p>En San Basilio del Palenque, nos comprometemos a proteger tu privacidad. Esta política describe cómo recopilamos, utilizamos y protegemos tu información personal.</p>
                    
                    <h5>1. Información que Recopilamos</h5>
                    <p>Recopilamos información personal como nombre, dirección, correo electrónico y datos de pago necesarios para procesar tus pedidos y mejorar tu experiencia de compra.</p>
                    
                    <h5>2. Uso de la Información</h5>
                    <p>Utilizamos tu información para procesar pedidos, comunicarnos contigo sobre tu compra, personalizar tu experiencia y mejorar nuestros servicios.</p>
                    
                    <h5>3. Protección de Datos</h5>
                    <p>Implementamos medidas de seguridad para proteger tu información personal contra acceso no autorizado, alteración, divulgación o destrucción.</p>
                    
                    <h5>4. Compartir Información</h5>
                    <p>No vendemos ni alquilamos tu información personal a terceros. Solo compartimos información con proveedores de servicios que nos ayudan a operar nuestro sitio y procesar tus pedidos.</p>
                    
                    <h5>5. Tus Derechos</h5>
                    <p>Tienes derecho a acceder, corregir o eliminar tu información personal. Para ejercer estos derechos, contáctanos a través de nuestro formulario de contacto.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor de notificaciones Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si hay un carrito en localStorage
            const localCart = localStorage.getItem('cart');
            
            // Si hay un carrito en localStorage y no se recibió uno por POST
            if (localCart && <?php echo isset($_POST['cart']) && !empty($_POST['cart']) ? 'false' : 'true'; ?>) {
                // Establecer el valor del campo oculto
                document.querySelector('input[name="cart"]').value = localCart;
                
                // Recargar la página con el carrito como parámetro POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cart';
                input.value = localCart;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
            
            // Mostrar/ocultar detalles de pago según método seleccionado
            const metodoPagoInputs = document.querySelectorAll('input[name="metodo_pago"]');
            const detallesTarjeta = document.getElementById('tarjeta_detalles');
            const detallesTransferencia = document.getElementById('transferencia_detalles');
            const detallesNequi = document.getElementById('nequi_detalles');
            
            metodoPagoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Ocultar todos los detalles
                    detallesTarjeta.style.display = 'none';
                    detallesTransferencia.style.display = 'none';
                    detallesNequi.style.display = 'none';
                    
                    // Mostrar detalles según método seleccionado
                    switch(this.value) {
                        case 'tarjeta':
                            detallesTarjeta.style.display = 'block';
                            break;
                        case 'transferencia':
                            detallesTransferencia.style.display = 'block';
                            break;
                        case 'nequi':
                            detallesNequi.style.display = 'block';
                            break;
                    }
                });
            });
            
            // Actualizar costo de envío según tipo seleccionado
            const tipoEnvioSelect = document.getElementById('tipo_envio');
            const costoEnvioSpan = document.getElementById('costo_envio');
            const granTotalSpan = document.getElementById('gran_total');
            const totalHidden = document.getElementById('total_hidden');
            
            tipoEnvioSelect.addEventListener('change', function() {
                let costoEnvio = <?php echo $envio; ?>;
                let subtotal = <?php echo $subtotal; ?>;
                let impuestos = <?php echo $impuestos; ?>;
                
                if (this.value === 'express') {
                    costoEnvio = <?php echo $envio; ?> + 10000; // Agregar costo de envío express
                }
                
                const nuevoTotal = subtotal + impuestos + costoEnvio;
                
                costoEnvioSpan.textContent = '$' + costoEnvio.toLocaleString('es-CO');
                granTotalSpan.textContent = '$' + nuevoTotal.toLocaleString('es-CO');
                totalHidden.value = nuevoTotal;
            });
            
            // Validación del formulario
            const checkoutForm = document.getElementById('checkoutForm');
            
            checkoutForm.addEventListener('submit', function(event) {
                // Validar método de pago
                const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
                if (!metodoPago) {
                    event.preventDefault();
                    showToast('Por favor selecciona un método de pago', 'error');
                    return;
                }
                
                // Validar campos de tarjeta si es el método seleccionado
                if (metodoPago.value === 'tarjeta') {
                    const tarjetaNombre = document.getElementById('tarjeta_nombre').value.trim();
                    const tarjetaNumero = document.getElementById('tarjeta_numero').value.trim();
                    const tarjetaExpiracion = document.getElementById('tarjeta_expiracion').value.trim();
                    const tarjetaCvv = document.getElementById('tarjeta_cvv').value.trim();
                    
                    if (!tarjetaNombre || !tarjetaNumero || !tarjetaExpiracion || !tarjetaCvv) {
                        event.preventDefault();
                        showToast('Por favor completa todos los datos de la tarjeta', 'error');
                        return;
                    }
                }
                
                // Validar términos y condiciones
                const terminos = document.getElementById('terminos');
                const politicaPrivacidad = document.getElementById('politica_privacidad');
                
                if (!terminos.checked || !politicaPrivacidad.checked) {
                    event.preventDefault();
                    showToast('Debes aceptar los términos y condiciones y la política de privacidad', 'error');
                    return;
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
        });
    </script>
</body>
</html>