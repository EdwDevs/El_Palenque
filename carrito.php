<?php
// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Verificar si el usuario está autenticado; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Almacenar el nombre del usuario logueado con seguridad contra XSS
$username = htmlspecialchars($_SESSION['usuario']);

// Asegurarse de que usuario_id esté disponible en la sesión
if (!isset($_SESSION['usuario_id'])) {
    // En una implementación real, obtendrías esto de la base de datos durante el inicio de sesión
    // Por ahora, asignamos un valor de ejemplo
    $_SESSION['usuario_id'] = 1;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Carrito de compras de San Basilio del Palenque">
    <title>Carrito de Compras - San Basilio del Palenque</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <style>
        :root {
            --color-primary: #FF5722;
            --color-secondary: #4CAF50;
            --color-accent: #FFC107;
            --color-text: #333333;
            --color-light: #FFFFFF;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            color: var(--color-text);
            margin: 0;
            padding: 0;
        }

        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-logo img {
            max-width: 120px;
            border-radius: 10px;
            border: 3px solid var(--color-primary);
            transition: var(--transition);
        }

        .header-logo img:hover {
            transform: scale(1.05);
        }

        .user-welcome {
            color: var(--color-primary);
            font-weight: bold;
            margin: 0 1rem;
        }

        .btn-home {
            background-color: var(--color-primary);
            color: var(--color-light);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-home:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
        }

        .main-content {
            margin-top: 8rem;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        h1 {
            color: var(--color-primary);
            text-align: center;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--color-primary);
            border-radius: 2px;
        }

        .cart-container {
            background: var(--color-light);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 2rem;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            transition: var(--transition);
        }

        .cart-item:hover {
            background-color: #f9f9f9;
            transform: translateX(5px);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }

        .cart-item-info {
            flex: 1;
            margin-left: 1rem;
        }

        .cart-item-info h4 {
            color: var(--color-primary);
            font-weight: 600;
            margin: 0;
        }

        .cart-item-info p {
            color: var(--color-text);
            margin: 0.5rem 0 0;
        }

        .cart-item-price {
            font-weight: bold;
            color: var(--color-secondary);
            margin-right: 1rem;
        }

        .btn-remove {
            background-color: #f44336;
            color: var(--color-light);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-remove:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .cart-summary {
            margin-top: 2rem;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .cart-summary p {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--color-text);
            margin: 0;
        }

        .cart-summary .total {
            color: var(--color-secondary);
            font-size: 1.5rem;
        }

        .btn-clear, .btn-checkout {
            background-color: var(--color-primary);
            color: var(--color-light);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-clear:hover, .btn-checkout:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
        }

        .empty-cart {
            text-align: center;
            color: var(--color-text);
            font-size: 1.2rem;
            padding: 2rem;
        }

        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: var(--color-light);
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .success { background-color: var(--color-secondary); }
        .error { background-color: #f44336; }

        @media (max-width: 768px) {
            header { flex-direction: column; gap: 1rem; padding: 1rem; }
            .main-content { padding: 1rem; }
            .cart-item { flex-direction: column; text-align: center; gap: 1rem; }
            .cart-item-image img { margin-right: 0; }
            .cart-item-info { margin-left: 0; }
            .cart-summary { text-align: center; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header>
        <div class="header-logo">
            <a href="index.php" title="Volver al inicio">
                <img src="imagenes/logo.jpeg" alt="San Basilio de Palenque">
            </a>
        </div>
        <span class="user-welcome"><i class="fas fa-user"></i> ¡Hola, <?php echo $username; ?>!</span>
        <a href="productos_compra.php" class="btn-home"><i class="fas fa-arrow-left"></i> Seguir Comprando</a>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <h1>Tu Carrito de Compras</h1>
        <div class="cart-container" id="cartContainer">
            <!-- Los productos se cargarán aquí dinámicamente con JavaScript -->
        </div>
        <!-- Notificación -->
        <div id="notification" class="notification"></div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
    // Cargar productos del carrito desde localStorage
    function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartContainer = document.getElementById('cartContainer');
    cartContainer.innerHTML = '';

    if (cart.length === 0) {
        cartContainer.innerHTML = '<p class="empty-cart">Tu carrito está vacío. ¡Agrega algunos productos!</p>';
        return;
    }

    let total = 0;

    cart.forEach((item, index) => {
        const cantidad = item.cantidad || 1; // Cantidad por defecto 1 si no está definida
        const subtotal = parseFloat(item.price) * cantidad;
        const itemElement = document.createElement('div');
        itemElement.classList.add('cart-item');
        itemElement.innerHTML = `
            <div class="cart-item-image">
                <img src="${item.imagen || 'https://via.placeholder.com/50'}" alt="${item.name}">
            </div>
            <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p>ID: ${item.id}</p>
                <input type="number" min="1" value="${cantidad}" onchange="updateQuantity(${index}, this.value)" style="width: 60px;">
            </div>
            <span class="cart-item-price">$${subtotal.toFixed(2)}</span>
            <button class="btn-remove" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i> Eliminar</button>
        `;
        cartContainer.appendChild(itemElement);
        total += subtotal;
    });

    const summary = document.createElement('div');
    summary.classList.add('cart-summary');
    summary.innerHTML = `
        <p>Total: <span class="total">$${total.toFixed(2)}</span></p>
        <button class="btn-clear" onclick="clearCart()">Vaciar Carrito</button>
        <button class="btn-checkout" onclick="checkout()">Finalizar Compra</button>
    `;
    cartContainer.appendChild(summary);
}

function updateQuantity(index, newQuantity) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart[index].cantidad = parseInt(newQuantity);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Recargar para actualizar el total
}

    // Eliminar un producto del carrito
    function removeFromCart(index) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }

    // Vaciar el carrito completo
    function clearCart() {
        if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
            localStorage.removeItem('cart');
            loadCart();
        }
    }

    // Finalizar compra enviando datos a procesar_pedido.php
    function checkout() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart.length === 0) {
            showNotification('El carrito está vacío', 'error');
            return;
        }

        if (confirm('¿Confirmas la compra?')) {
            fetch('procesar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cart: cart })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    localStorage.removeItem('cart');
                    showNotification('¡Compra realizada con éxito! Gracias por tu pedido.', 'success');
                    setTimeout(() => loadCart(), 1500);
                } else {
                    showNotification('Error al procesar el pedido: ' + data.message, 'error');
                }
            })
            .catch(error => showNotification('Error de conexión: ' + error, 'error'));
        }
    }

    // Mostrar notificaciones
    function showNotification(message, type) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.style.display = 'block';
        setTimeout(() => notification.style.display = 'none', 3000);
    }

    // Cargar el carrito al iniciar la página
    document.addEventListener('DOMContentLoaded', loadCart);
</script>
</body>
</html>