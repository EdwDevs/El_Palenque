<?php
// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Verificar si el usuario está autenticado; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?redirect=carrito.php");
    exit();
}

// Almacenar el nombre del usuario logueado con seguridad contra XSS
$username = htmlspecialchars($_SESSION['usuario']);

// Asegurarse de que usuario_id esté disponible en la sesión
if (!isset($_SESSION['usuario_id'])) {
    // Esto debería manejarse en validar_inicio.php, no aquí
    $_SESSION['usuario_id'] = 1; // Temporal
}

// Incluir el archivo de conexión a la base de datos
include('db.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Carrito de compras de San Basilio del Palenque">
    <title>Carrito de Compras - San Basilio del Palenque</title>

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

        /* Carrito de compras */
        .cart-container {
            background-color: var(--color-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
            animation: fadeIn 0.5s ease-in-out;
        }

        .cart-container:hover {
            box-shadow: var(--shadow-lg);
        }

        .cart-header {
            background-color: var(--color-primary-light);
            color: var(--color-light);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .cart-count {
            background-color: var(--color-light);
            color: var(--color-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .cart-body {
            padding: var(--spacing-md);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: var(--spacing-md);
            border-bottom: 1px solid var(--color-light-gray);
            transition: var(--transition);
            animation: slideIn 0.3s ease-in-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
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

        .cart-item:hover {
            background-color: var(--color-light-gray);
            transform: translateX(5px);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            overflow: hidden;
            border-radius: var(--border-radius);
            margin-right: var(--spacing-md);
            box-shadow: var(--shadow-sm);
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .cart-item:hover .cart-item-image img {
            transform: scale(1.1);
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            color: var(--color-text);
            margin: 0 0 0.25rem;
            font-size: 1.1rem;
        }

        .cart-item-meta {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--color-text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            font-weight: 700;
            color: var(--color-secondary-dark);
            font-size: 1.1rem;
            margin-right: var(--spacing-md);
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid var(--color-light-gray);
            background-color: var(--color-light);
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background-color: var(--color-primary-light);
            color: var(--color-light);
        }

        .quantity-input {
            width: 50px;
            height: 30px;
            border: 1px solid var(--color-light-gray);
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }

        .cart-item-subtotal {
            font-weight: 700;
            color: var(--color-primary);
            font-size: 1.2rem;
            margin: 0 var(--spacing-md);
            min-width: 100px;
            text-align: right;
        }

        .cart-item-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-remove {
            background-color: var(--color-danger);
            color: var(--color-light);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-remove:hover {
            background-color: var(--color-danger-dark);
            transform: rotate(90deg);
        }

        .cart-footer {
            background-color: var(--color-light-gray);
            padding: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }

        .cart-summary {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-summary-label {
            font-weight: 600;
            color: var(--color-text);
        }

        .cart-summary-value {
            font-weight: 700;
            color: var(--color-secondary-dark);
        }

        .cart-total {
            font-size: 1.5rem;
            color: var(--color-primary-dark);
        }

        .cart-actions {
            display: flex;
            gap: var(--spacing-sm);
        }

        .empty-cart {
            padding: var(--spacing-xl);
            text-align: center;
            color: var(--color-text-light);
        }

        .empty-cart-icon {
            font-size: 4rem;
            color: var(--color-light-gray);
            margin-bottom: var(--spacing-md);
        }

        .empty-cart-message {
            font-size: 1.2rem;
            margin-bottom: var(--spacing-md);
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

        /* Responsive */
        @media (max-width: 992px) {
            .cart-item {
                flex-wrap: wrap;
            }

            .cart-item-subtotal {
                margin-top: var(--spacing-sm);
                text-align: left;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: var(--spacing-md);
            }

            .page-title {
                font-size: 1.8rem;
            }

            .cart-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .cart-actions {
                flex-direction: column;
            }

            .checkout-steps {
                flex-direction: column;
                gap: var(--spacing-md);
                align-items: flex-start;
            }

            .checkout-steps::before {
                display: none;
            }

            .checkout-step {
                flex-direction: row;
                gap: var(--spacing-sm);
            }

            .step-number {
                margin-bottom: 0;
            }
        }

        @media (max-width: 576px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .cart-item-image {
                margin-right: 0;
                margin-bottom: var(--spacing-sm);
            }

            .cart-item-actions {
                margin-top: var(--spacing-sm);
                align-self: flex-end;
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
                        <a class="nav-link" href="Historias_comunidad.php">Historias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacto.php">Contacto</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="user-welcome d-none d-md-flex">
                        <i class="fas fa-user-circle"></i> Hola, <?php echo $username; ?>
                    </span>

                    <a href="productos_compra.php" class="btn-action">
                        <i class="fas fa-shopping-basket"></i> Seguir comprando
                    </a>

                    <a href="logout.php" class="btn-action btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container main-content">
        <h1 class="page-title animate__animated animate__fadeIn">Tu Carrito de Compras</h1>

        <!-- Pasos del proceso de compra -->
        <div class="checkout-steps animate__animated animate__fadeIn">
            <div class="checkout-step active">
                <div class="step-number">1</div>
                <div class="step-label">Carrito</div>
            </div>
            <div class="checkout-step">
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

        <!-- Contenedor del carrito -->
        <div class="cart-container animate__animated animate__fadeIn" id="cartContainer">
            <!-- El contenido del carrito se cargará dinámicamente con JavaScript -->
        </div>
    </main>

    <!-- Formulario oculto para enviar el carrito a finalizar_compra.php -->
    <form id="checkoutForm" action="finalizar_compra.php" method="post" style="display: none;">
        <input type="hidden" name="cart" id="cartInput">
        <input type="hidden" name="total" id="totalInput">
        <input type="hidden" name="subtotal" id="subtotalInput">
        <input type="hidden" name="impuestos" id="impuestosInput">
    </form>

    <!-- Contenedor de notificaciones Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar el carrito al iniciar
            loadCart();

            // Verificar si hay mensajes de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            const status = urlParams.get('status');

            if (message) {
                showToast(message, status || 'success');
            }
        });

        // Función para cargar el carrito
        function loadCart() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const cartContainer = document.getElementById('cartContainer');

            // Si el carrito está vacío
            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="empty-cart-message">Tu carrito está vacío</h3>
                        <p>¿No sabes qué comprar? ¡Millones de productos te esperan!</p>
                        <a href="productos_compra.php" class="btn-action mt-3">
                            <i class="fas fa-shopping-basket"></i> Explorar productos
                        </a>
                    </div>
                `;
                return;
            }

            // Calcular totales
            let subtotal = 0;
            let itemCount = 0;

            // Crear estructura del carrito
            let cartHTML = `
                <div class="cart-header">
                    <h2>Productos en tu carrito</h2>
                    <span class="cart-count">${cart.length} ${cart.length === 1 ? 'producto' : 'productos'}</span>
                </div>
                <div class="cart-body">
            `;

            // Agregar cada producto
            cart.forEach((item, index) => {
                const cantidad = item.cantidad || 1;
                const precio = parseFloat(item.precio || item.price);
                const itemSubtotal = precio * cantidad;
                subtotal += itemSubtotal;
                itemCount += cantidad;

                cartHTML += `
                    <div class="cart-item" data-index="${index}">
                        <div class="cart-item-image">
                            <img src="${item.imagen || 'https://via.placeholder.com/80'}" alt="${item.nombre || item.name}">
                        </div>
                        <div class="cart-item-details">
                            <h3 class="cart-item-title">${item.nombre || item.name}</h3>
                            <div class="cart-item-meta">
                                <span>ID: ${item.id}</span>
                            </div>
                            <div class="cart-item-price">$${precio.toLocaleString('es-CO')}</div>
                        </div>
                        <div class="cart-item-quantity">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(${index}, ${cantidad - 1})" ${cantidad <= 1 ? 'disabled' : ''}>
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="quantity-input" value="${cantidad}" min="1" max="10" 
                                   onchange="updateQuantity(${index}, this.value)">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(${index}, ${cantidad + 1})" ${cantidad >= 10 ? 'disabled' : ''}>
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="cart-item-subtotal">$${itemSubtotal.toLocaleString('es-CO')}</div>
                        <div class="cart-item-actions">
                            <button type="button" class="btn-remove" onclick="removeFromCart(${index})" title="Eliminar producto">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            // Calcular impuestos y total
            const impuestos = subtotal * 0.19; // 19% IVA
            const total = subtotal + impuestos;

            // Agregar resumen y botones
            cartHTML += `
                </div>
                <div class="cart-footer">
                    <div class="cart-summary">
                        <div class="cart-summary-row">
                            <span class="cart-summary-label">Subtotal:</span>
                            <span class="cart-summary-value">$${subtotal.toLocaleString('es-CO')}</span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="cart-summary-label">IVA (19%):</span>
                            <span class="cart-summary-value">$${impuestos.toLocaleString('es-CO')}</span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="cart-summary-label cart-total">Total:</span>
                            <span class="cart-summary-value cart-total">$${total.toLocaleString('es-CO')}</span>
                        </div>
                    </div>
                    <div class="cart-actions">
                        <button type="button" class="btn-action btn-danger" onclick="clearCart()">
                            <i class="fas fa-trash"></i> Vaciar carrito
                        </button>
                        <button type="button" class="btn-action btn-secondary" onclick="checkout()">
                            <i class="fas fa-check-circle"></i> Finalizar compra
                        </button>
                    </div>
                </div>
            `;

            // Actualizar el contenedor
            cartContainer.innerHTML = cartHTML;

            // Guardar valores para el checkout
            document.getElementById('totalInput').value = total;
            document.getElementById('subtotalInput').value = subtotal;
            document.getElementById('impuestosInput').value = impuestos;
        }

        // Función para actualizar cantidad
        function updateQuantity(index, newQuantity) {
            newQuantity = parseInt(newQuantity);

            // Validar cantidad
            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
            } else if (newQuantity > 10) {
                newQuantity = 10;
                showToast('Máximo 10 unidades por producto', 'error');
            }

            // Obtener carrito actual
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Actualizar cantidad
            cart[index].cantidad = newQuantity;

            // Guardar carrito actualizado
            localStorage.setItem('cart', JSON.stringify(cart));

            // Recargar carrito
            loadCart();

            // Mostrar notificación
            showToast(`Cantidad actualizada: ${newQuantity}`, 'success');
        }

        // Función para eliminar producto del carrito
        function removeFromCart(index) {
            // Obtener carrito actual
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Guardar nombre del producto antes de eliminarlo
            const productName = cart[index].nombre || cart[index].name;

            // Eliminar producto
            cart.splice(index, 1);

            // Guardar carrito actualizado
            localStorage.setItem('cart', JSON.stringify(cart));

            // Recargar carrito
            loadCart();

            // Mostrar notificación
            showToast(`"${productName}" eliminado del carrito`, 'success');
        }

        // Función para vaciar carrito
        function clearCart() {
            // Confirmar acción
            if (!confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
                return;
            }

            // Vaciar carrito
            localStorage.removeItem('cart');

            // Recargar carrito
            loadCart();

            // Mostrar notificación
            showToast('Carrito vaciado correctamente', 'success');
        }

        // Función para finalizar compra
        function checkout() {
            // Obtener carrito actual
            const cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Verificar si hay productos
            if (cart.length === 0) {
                showToast('No hay productos en el carrito', 'error');
                return;
            }

            // Calcular totales nuevamente para asegurar datos actualizados
            let subtotal = 0;
            cart.forEach(item => {
                const cantidad = parseInt(item.cantidad || 1);
                const precio = parseFloat(item.precio || item.price);
                subtotal += precio * cantidad;
            });

            const impuestos = subtotal * 0.19; // 19% IVA
            const total = subtotal + impuestos;

            // Preparar datos para enviar
            document.getElementById('cartInput').value = JSON.stringify(cart);
            document.getElementById('totalInput').value = total;
            document.getElementById('subtotalInput').value = subtotal;
            document.getElementById('impuestosInput').value = impuestos;

            // Guardar en sessionStorage como respaldo
            sessionStorage.setItem('checkout_cart', JSON.stringify(cart));
            sessionStorage.setItem('checkout_total', total);
            sessionStorage.setItem('checkout_subtotal', subtotal);
            sessionStorage.setItem('checkout_impuestos', impuestos);

            // Enviar formulario
            document.getElementById('checkoutForm').submit();
        }

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

        // Función para sincronizar carrito con la barra de navegación
        function syncCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const count = cart.reduce((total, item) => total + (parseInt(item.cantidad) || 1), 0);

            // Actualizar contador en la barra de navegación si existe
            const navCartCount = document.getElementById('cartCountNav');
            if (navCartCount) {
                navCartCount.textContent = count;
                navCartCount.style.display = count > 0 ? 'flex' : 'none';
            }
        }

        // Sincronizar carrito al cargar
        syncCartCount();

        // Escuchar cambios en localStorage para mantener sincronizado
        window.addEventListener('storage', function(e) {
            if (e.key === 'cart') {
                loadCart();
                syncCartCount();
            }
        });
    </script>
</body>

</html>