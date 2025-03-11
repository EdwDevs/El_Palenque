<?php
// Iniciar sesión para gestionar datos del usuario
session_start();

// Verificar si el usuario está autenticado
$isLoggedIn = isset($_SESSION['usuario']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['usuario']) : 'Invitado';

// Incluir el archivo de conexión a la base de datos
include('db.php');

// Consultar productos con sus categorías usando un LEFT JOIN
$query = "SELECT p.*, c.nombre_categoria AS categoria 
          FROM productos p 
          LEFT JOIN categorias c ON p.categoria_id = c.categoria_id 
          ORDER BY c.nombre_categoria, p.nombre";
$result = $conexion->query($query);

// Verificar si la consulta falló
if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}

// Consultar todas las categorías para el filtro y navegación
$category_query = "SELECT * FROM categorias";
$category_result = $conexion->query($category_query);

// Organizar productos por categoría para mostrarlos en secciones
$products_by_category = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cat_id = $row['categoria_id'] ?? 0;
        $cat_name = $row['categoria'] ?? 'Sin categoría';
        
        if (!isset($products_by_category[$cat_id])) {
            $products_by_category[$cat_id] = [
                'name' => $cat_name,
                'products' => []
            ];
        }
        
        $products_by_category[$cat_id]['products'][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Compra artesanías, plantas medicinales e instrumentos musicales de San Basilio del Palenque">
    <title>Productos - San Basilio del Palenque</title>

    <!-- Preconexión a CDNs para mejorar rendimiento -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <!-- Bootstrap CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Montserrat y Poppins para una tipografía elegante -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap"
        rel="stylesheet">

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
        color: var(--color-text);
        margin: 0;
        padding: 0;
        position: relative;
        min-height: 100vh;
        padding-bottom: 60px;
        /* Espacio para el footer */
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

    .user-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-welcome {
        color: var(--color-primary);
        font-weight: 600;
        margin-right: 1rem;
    }

    .btn-auth {
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
    }

    .btn-auth:hover {
        background-color: var(--color-primary-dark);
        color: var(--color-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .cart-btn {
        position: relative;
    }

    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: var(--color-accent);
        color: var(--color-text);
        font-size: 0.7rem;
        font-weight: bold;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: var(--transition);
    }

    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary), var(--color-primary-dark));
        color: var(--color-light);
        padding: var(--spacing-xl) 0;
        margin-top: 76px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://images.unsplash.com/photo-1519074069444-1ba4fff66d16?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-position: center;
        opacity: 0.2;
        z-index: 0;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-title {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero-subtitle {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto var(--spacing-lg);
        opacity: 0.9;
    }

    /* Filtros y Búsqueda */
    .filter-section {
        background-color: var(--color-light);
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--spacing-lg);
        transition: var(--transition);
    }

    .filter-section:hover {
        box-shadow: var(--shadow-lg);
    }

    .search-input {
        border: 2px solid var(--color-light-gray);
        border-radius: var(--border-radius);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: var(--transition);
        width: 100%;
    }

    .search-input:focus {
        border-color: var(--color-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.2);
    }

    .filter-select {
        border: 2px solid var(--color-light-gray);
        border-radius: var(--border-radius);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: var(--transition);
        background-color: var(--color-light);
        cursor: pointer;
    }

    .filter-select:focus {
        border-color: var(--color-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.2);
    }

    .filter-badge {
        background-color: var(--color-light-gray);
        color: var(--color-text);
        border-radius: 20px;
        padding: 0.3rem 0.8rem;
        margin: 0.25rem;
        display: inline-block;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.9rem;
    }

    .filter-badge:hover,
    .filter-badge.active {
        background-color: var(--color-primary);
        color: var(--color-light);
    }

    /* Categorías y Productos */
    .category-section {
        margin-bottom: var(--spacing-xl);
        scroll-margin-top: 100px;
    }

    .category-header {
        display: flex;
        align-items: center;
        margin-bottom: var(--spacing-md);
        padding-bottom: var(--spacing-xs);
        border-bottom: 2px solid var(--color-primary-light);
    }

    .category-icon {
        font-size: 1.5rem;
        color: var(--color-primary);
        margin-right: var(--spacing-sm);
    }

    .category-title {
        color: var(--color-primary-dark);
        margin: 0;
        font-size: 1.8rem;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--spacing-md);
    }

    .product-card {
        background-color: var(--color-light);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: var(--color-accent);
        color: var(--color-text);
        font-size: 0.8rem;
        font-weight: bold;
        padding: 0.3rem 0.6rem;
        border-radius: 20px;
        z-index: 2;
    }

    .product-image-container {
        position: relative;
        overflow: hidden;
        height: 200px;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0.7) 100%);
        opacity: 0;
        transition: var(--transition);
    }

    .product-card:hover .product-overlay {
        opacity: 1;
    }

    .quick-view-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        background-color: var(--color-light);
        color: var(--color-primary);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: var(--transition);
        cursor: pointer;
    }

    .product-card:hover .quick-view-btn {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    .product-content {
        padding: var(--spacing-md);
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--color-text);
        margin-top: 0;
        margin-bottom: var(--spacing-xs);
        transition: var(--transition);
    }

    .product-card:hover .product-title {
        color: var(--color-primary);
    }

    .product-category {
        font-size: 0.85rem;
        color: var(--color-text-light);
        margin-bottom: var(--spacing-xs);
    }

    .product-description {
        font-size: 0.9rem;
        color: var(--color-text);
        margin-bottom: var(--spacing-sm);
        flex-grow: 1;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .product-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--color-secondary-dark);
        margin: 0;
    }

    .product-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-add-cart,
    .btn-wishlist {
        border: none;
        background-color: var(--color-secondary);
        color: var(--color-light);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: var(--transition);
    }

    .btn-add-cart:hover {
        background-color: var(--color-secondary-dark);
        transform: translateY(-2px);
    }

    .btn-wishlist {
        background-color: var(--color-light-gray);
        color: var(--color-text);
    }

    .btn-wishlist:hover,
    .btn-wishlist.active {
        background-color: #e91e63;
        color: var(--color-light);
    }

    .btn-add-cart.added {
        background-color: var(--color-secondary-dark);
        animation: pulse 1s;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1);
        }
    }

    /* Modal de Vista Rápida */
    .modal-content {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background-color: var(--color-primary);
        color: var(--color-light);
        border-bottom: none;
    }

    .modal-title {
        font-weight: 700;
    }

    .modal-body {
        padding: var(--spacing-lg);
    }

    .modal-product-image {
        width: 100%;
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-md);
    }

    .modal-product-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-secondary-dark);
        margin-bottom: var(--spacing-md);
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        margin-bottom: var(--spacing-md);
    }

    .quantity-btn {
        width: 36px;
        height: 36px;
        border: 1px solid var(--color-light-gray);
        background-color: var(--color-light);
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: var(--transition);
    }

    .quantity-btn:hover {
        background-color: var(--color-light-gray);
    }

    .quantity-input {
        width: 50px;
        height: 36px;
        border: 1px solid var(--color-light-gray);
        text-align: center;
        font-weight: 600;
        margin: 0 5px;
    }

    .modal-add-cart {
        background-color: var(--color-secondary);
        color: var(--color-light);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-add-cart:hover {
        background-color: var(--color-secondary-dark);
        transform: translateY(-2px);
    }

    /* Carrito Flotante */
    .floating-cart {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: var(--color-secondary);
        color: var(--color-light);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: var(--shadow-lg);
        cursor: pointer;
        transition: var(--transition);
        z-index: 999;
    }

    .floating-cart:hover {
        transform: scale(1.1);
        background-color: var(--color-secondary-dark);
    }

    .floating-cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: var(--color-accent);
        color: var(--color-text);
        font-size: 0.8rem;
        font-weight: bold;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Toast Notifications */
    .toast-container {
        position: fixed;
        bottom: 20px;
        left: 20px;
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
        animation: slideIn 0.3s forwards;
    }

    @keyframes slideIn {
        from {
            transform: translateX(-100%);
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
        background-color: var(--color-secondary-light);
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--color-light);
        font-size: 1.2rem;
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
    .custom-footer {
        background-color: var(--color-dark-gray);
        color: var(--color-light);
        padding: var(--spacing-md) 0;
        position: absolute;
        bottom: 0;
        width: 100%;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .footer-text {
        margin: 0;
        font-size: 0.9rem;
    }

    .footer-links {
        display: flex;
        gap: 1rem;
    }

    .footer-link {
        color: var(--color-light);
        text-decoration: none;
        transition: var(--transition);
    }

    .footer-link:hover {
        color: var(--color-primary-light);
    }

    /* Animaciones */
    .animate__animated {
        animation-duration: 0.8s;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .custom-navbar {
            padding: 0.5rem 1rem;
        }

        .navbar-brand img {
            max-width: 50px;
        }

        .hero-section {
            padding: var(--spacing-lg) 0;
            margin-top: 66px;
        }

        .hero-title {
            font-size: 1.8rem;
        }

        .category-title {
            font-size: 1.5rem;
        }

        .footer-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        .products-grid {
            grid-template-columns: 1fr;
        }

        .filter-section {
            padding: var(--spacing-sm);
        }

        .product-card {
            max-width: 320px;
            margin: 0 auto;
        }

        .floating-cart {
            width: 50px;
            height: 50px;
            bottom: 15px;
            right: 15px;
        }
    }

    /* Utilidades */
    .hidden {
        display: none !important;
    }

    .no-results {
        text-align: center;
        padding: var(--spacing-lg);
        background-color: var(--color-light);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
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
                        <a class="nav-link active" href="productos_compra.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Historias_comunidad.php">Historias</a>
                    </li>
                    
                </ul>

                <div class="user-actions">
                    <span class="user-welcome d-none d-md-block">Hola, <?php echo $username; ?></span>

                    <?php if ($isLoggedIn): ?>
                    <a href="carrito.php" class="btn-auth cart-btn">
                        <i class="fas fa-shopping-cart"></i> Carrito
                        <span class="cart-count" id="cartCountNav">0</span>
                    </a>
                    <a href="logout.php" class="btn-auth">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                    <?php else: ?>
                    <a href="login.php" class="btn-auth">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content animate__animated animate__fadeIn">
                <h1 class="hero-title">Productos de San Basilio de Palenque</h1>
                <p class="hero-subtitle">Descubre nuestra selección de artesanías, plantas medicinales e instrumentos
                    musicales tradicionales, elaborados con técnicas ancestrales que preservan nuestra cultura.</p>
            </div>
        </div>
    </section>

    <!-- Contenido principal -->
    <main class="container py-5">
        <!-- Filtros y Búsqueda -->
        <section class="filter-section animate__animated animate__fadeInUp">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="search-input border-start-0"
                            placeholder="Buscar productos...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select id="categoryFilter" class="filter-select form-select">
                        <option value="">Todas las categorías</option>
                        <?php
                        // Rellenar el filtro de categorías dinámicamente
                        $category_result->data_seek(0); // Reiniciar el puntero del resultado
                        while ($cat = $category_result->fetch_assoc()) {
                            $cat_id = htmlspecialchars($cat['categoria_id']);
                            $cat_name = htmlspecialchars($cat['nombre_categoria']);
                            echo '<option value="' . $cat_id . '">' . $cat_name . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="sortFilter" class="filter-select form-select">
                        <option value="">Ordenar por</option>
                        <option value="price-desc">Precio: Mayor a Menor</option>
                        <option value="name-asc">Nombre: A-Z</option>
                        <option value="name-desc">Nombre: Z-A</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <div class="d-flex flex-wrap" id="filterTags">
                    <!-- Los filtros activos se mostrarán aquí dinámicamente -->
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="filter-badges" id="categoryBadges">
                    <span class="filter-badge active" data-category="">Todos</span>
                    <?php
                    // Mostrar badges de categorías para filtrado rápido
                    $category_result->data_seek(0);
                    while ($cat = $category_result->fetch_assoc()) {
                        $cat_id = htmlspecialchars($cat['categoria_id']);
                        $cat_name = htmlspecialchars($cat['nombre_categoria']);
                        echo '<span class="filter-badge" data-category="' . $cat_id . '">' . $cat_name . '</span>';
                    }
                    ?>
                </div>

                <div class="results-count">
                    <span id="productCount">0</span> productos encontrados
                </div>
            </div>
        </section>

        <!-- Contenedor para mensajes de no resultados -->
        <div id="noResults" class="no-results hidden animate__animated animate__fadeIn">
            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
            <h3>No se encontraron productos</h3>
            <p>Intenta con otros términos de búsqueda o elimina los filtros aplicados.</p>
            <button id="clearFilters" class="btn btn-outline-primary mt-2">
                <i class="fas fa-times-circle me-2"></i>Limpiar filtros
            </button>
        </div>

        <!-- Productos por Categoría -->
        <?php foreach ($products_by_category as $cat_id => $category): ?>
        <section class="category-section animate__animated animate__fadeInUp"
            id="<?php echo strtolower(str_replace(' ', '-', $category['name'])); ?>">
            <div class="category-header">
                <i class="category-icon fas <?php 
                    // Asignar icono según la categoría
                    $icon = 'fa-box';
                    switch(strtolower($category['name'])) {
                        case 'artesanías': $icon = 'fa-hands-holding'; break;
                        case 'instrumentos musicales': $icon = 'fa-drum'; break;
                        case 'plantas medicinales': $icon = 'fa-leaf'; break;
                        case 'dulces tradicionales': $icon = 'fa-candy-cane'; break;
                        case 'accesorios': $icon = 'fa-hat-cowboy'; break;
                    }
                    echo $icon;
                ?>"></i>
                <h2 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h2>
            </div>

            <div class="products-grid" data-category="<?php echo $cat_id; ?>">
                <?php foreach ($category['products'] as $product): ?>
                <div class="product-card" data-id="<?php echo htmlspecialchars($product['id']); ?>"
                    data-name="<?php echo htmlspecialchars($product['nombre']); ?>"
                    data-category="<?php echo htmlspecialchars($product['categoria_id']); ?>"
                    data-price="<?php echo htmlspecialchars($product['precio']); ?>"
                    data-description="<?php echo htmlspecialchars($product['descripcion'] ?? ''); ?>"
                    data-image="<?php echo htmlspecialchars($product['imagen'] ?? 'https://via.placeholder.com/300x200'); ?>">

                    <?php if (isset($product['destacado']) && $product['destacado']): ?>
                    <div class="product-badge">Destacado</div>
                    <?php endif; ?>

                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($product['imagen'] ?? 'https://via.placeholder.com/300x200'); ?>"
                            alt="<?php echo htmlspecialchars($product['nombre']); ?>" class="product-image"
                            loading="lazy">
                        <div class="product-overlay"></div>
                        <button type="button" class="quick-view-btn" data-bs-toggle="modal"
                            data-bs-target="#quickViewModal"
                            data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['nombre']); ?></h3>
                        <p class="product-category">
                            <i class="fas <?php echo $icon; ?> me-1"></i>
                            <?php echo htmlspecialchars($product['categoria']); ?>
                        </p>
                        <p class="product-description">
                            <?php 
                            $desc = $product['descripcion'] ?? 'Sin descripción';
                            echo (strlen($desc) > 100) ? htmlspecialchars(substr($desc, 0, 100)) . '...' : htmlspecialchars($desc); 
                            ?>
                        </p>

                        <div class="product-footer">
                            <p class="product-price">$<?php echo number_format($product['precio'], 2, ',', '.'); ?></p>
                            <div class="product-actions">
                                <button type="button" class="btn-wishlist"
                                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button type="button" class="btn-add-cart"
                                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>

    <!-- Modal de Vista Rápida -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickViewModalLabel">Vista Rápida del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="" alt="Imagen del producto" id="modalProductImage" class="modal-product-image">
                        </div>
                        <div class="col-md-6">
                            <h3 id="modalProductTitle"></h3>
                            <p class="product-category" id="modalProductCategory"></p>
                            <div class="modal-product-price" id="modalProductPrice"></div>
                            <p id="modalProductDescription"></p>

                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn" id="decreaseQuantity">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="productQuantity" class="quantity-input" value="1" min="1"
                                    max="10">
                                <button type="button" class="quantity-btn" id="increaseQuantity">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            <button type="button" class="modal-add-cart" id="modalAddToCart">
                                <i class="fas fa-cart-plus me-2"></i>
                                Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrito Flotante -->
    <div class="floating-cart" id="floatingCart">
        <i class="fas fa-shopping-cart"></i>
        <span class="floating-cart-count" id="floatingCartCount">0</span>
    </div>

    <!-- Contenedor de Notificaciones Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Footer -->
    <footer class="custom-footer">
        <div class="container">
            <div class="footer-content">
                <p class="footer-text">© <?php echo date('Y'); ?> San Basilio de Palenque - Hecho con orgullo en
                    Colombia</p>
                <div class="footer-links">
                    <a href="index.php" class="footer-link">Inicio</a>
                    <a href="tradiciones.php" class="footer-link">Tradiciones</a>
                    <a href="productos_compra.php" class="footer-link">Productos</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const sortFilter = document.getElementById('sortFilter');
        const categoryBadges = document.querySelectorAll('.filter-badge');
        const productCards = document.querySelectorAll('.product-card');
        const noResults = document.getElementById('noResults');
        const clearFiltersBtn = document.getElementById('clearFilters');
        const productCount = document.getElementById('productCount');
        const filterTags = document.getElementById('filterTags');
        const quickViewModal = document.getElementById('quickViewModal');
        const floatingCart = document.getElementById('floatingCart');

        // Carrito de compras
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        updateCartCount();

        // Inicializar conteo de productos
        updateProductCount();

        // Función para filtrar productos
        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value;
            const sortOption = sortFilter.value;
            let visibleCount = 0;
            let activeFilters = [];

            // Recopilar filtros activos
            if (searchTerm) {
                activeFilters.push({
                    type: 'search',
                    value: searchTerm,
                    label: `Búsqueda: "${searchTerm}"`
                });
            }

            if (selectedCategory) {
                const categoryName = categoryFilter.options[categoryFilter.selectedIndex].text;
                activeFilters.push({
                    type: 'category',
                    value: selectedCategory,
                    label: `Categoría: ${categoryName}`
                });
            }

            if (sortOption) {
                const sortName = sortFilter.options[sortFilter.selectedIndex].text;
                activeFilters.push({
                    type: 'sort',
                    value: sortOption,
                    label: `${sortName}`
                });
            }

            // Actualizar etiquetas de filtro
            updateFilterTags(activeFilters);

            // Filtrar productos
            productCards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const category = card.getAttribute('data-category');
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesCategory = !selectedCategory || category === selectedCategory;

                if (matchesSearch && matchesCategory) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Ordenar productos visibles
            if (sortOption) {
                sortProducts(sortOption);
            }

            // Actualizar contador y mostrar mensaje si no hay resultados
            updateProductCount(visibleCount);
            toggleNoResultsMessage(visibleCount === 0);

            // Actualizar badges de categoría activa
            updateCategoryBadges(selectedCategory);
        }

        // Función para ordenar productos
        function sortProducts(sortOption) {
            const productsGrid = document.querySelectorAll('.products-grid');

            productsGrid.forEach(grid => {
                const products = Array.from(grid.querySelectorAll('.product-card:not(.hidden)'));

                products.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    const nameA = a.getAttribute('data-name').toLowerCase();
                    const nameB = b.getAttribute('data-name').toLowerCase();

                    switch (sortOption) {
                        case 'price-asc':
                            return priceA - priceB;
                        case 'price-desc':
                            return priceB - priceA;
                        case 'name-asc':
                            return nameA.localeCompare(nameB);
                        case 'name-desc':
                            return nameB.localeCompare(nameA);
                        default:
                            return 0;
                    }
                });

                // Reordenar en el DOM
                products.forEach(product => {
                    grid.appendChild(product);
                });
            });
        }

        // Función para actualizar etiquetas de filtro
        function updateFilterTags(filters) {
            filterTags.innerHTML = '';

            filters.forEach(filter => {
                const tag = document.createElement('div');
                tag.className = 'badge bg-primary me-2 mb-2 p-2';
                tag.innerHTML = `
                        ${filter.label}
                        <button type="button" class="btn-close btn-close-white ms-2" 
                                aria-label="Eliminar filtro" data-filter-type="${filter.type}"></button>
                    `;
                filterTags.appendChild(tag);
            });

            // Agregar eventos a los botones de cierre
            document.querySelectorAll('.btn-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filterType = this.getAttribute('data-filter-type');
                    removeFilter(filterType);
                });
            });
        }

        // Función para eliminar un filtro
        function removeFilter(filterType) {
            switch (filterType) {
                case 'search':
                    searchInput.value = '';
                    break;
                case 'category':
                    categoryFilter.value = '';
                    updateCategoryBadges('');
                    break;
                case 'sort':
                    sortFilter.value = '';
                    break;
            }

            filterProducts();
        }

        // Función para actualizar badges de categoría
        function updateCategoryBadges(selectedCategory) {
            categoryBadges.forEach(badge => {
                const category = badge.getAttribute('data-category');
                if ((!selectedCategory && category === '') || category === selectedCategory) {
                    badge.classList.add('active');
                } else {
                    badge.classList.remove('active');
                }
            });
        }

        // Función para mostrar/ocultar mensaje de no resultados
        function toggleNoResultsMessage(show) {
            if (show) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // Función para actualizar contador de productos
        function updateProductCount(count) {
            const visibleCount = count !== undefined ? count :
                document.querySelectorAll('.product-card:not(.hidden)').length;
            productCount.textContent = visibleCount;
        }

        // Función para actualizar contador del carrito
        function updateCartCount() {
            const count = cart.reduce((total, item) => total + (item.cantidad || 1), 0);
            document.getElementById('cartCountNav').textContent = count;
            document.getElementById('floatingCartCount').textContent = count;
        }

        // Función para agregar al carrito
        function addToCart(productId, quantity = 1) {
            const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
            if (!productCard) return;

            const product = {
                id: productId,
                nombre: productCard.getAttribute('data-name'),
                precio: parseFloat(productCard.getAttribute('data-price')),
                imagen: productCard.getAttribute('data-image'),
                cantidad: quantity
            };

            // Verificar si el producto ya está en el carrito
            const existingItemIndex = cart.findIndex(item => item.id === productId);

            if (existingItemIndex >= 0) {
                // Incrementar cantidad si ya existe
                cart[existingItemIndex].cantidad += quantity;
            } else {
                // Agregar nuevo producto
                cart.push(product);
            }

            // Guardar en localStorage
            localStorage.setItem('cart', JSON.stringify(cart));

            // Actualizar contador
            updateCartCount();

            // Mostrar notificación
            showToast(product.nombre, quantity);

            // Animar botón
            const addButton = productCard.querySelector('.btn-add-cart');
            addButton.classList.add('added');
            setTimeout(() => {
                addButton.classList.remove('added');
            }, 1000);
        }

        // Función para mostrar notificación toast
        function showToast(productName, quantity) {
            const toast = document.createElement('div');
            toast.className = 'custom-toast animate__animated animate__fadeInLeft';
            toast.innerHTML = `
                    <div class="toast-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="toast-content">
                        <h5 class="toast-title">Producto agregado</h5>
                        <p class="toast-message">${quantity} x ${productName}</p>
                    </div>
                    <button type="button" class="toast-close">&times;</button>
                `;

            document.getElementById('toastContainer').appendChild(toast);

            // Auto-cerrar después de 3 segundos
            setTimeout(() => {
                toast.classList.remove('animate__fadeInLeft');
                toast.classList.add('animate__fadeOutLeft');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);

            // Evento para cerrar manualmente
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.classList.remove('animate__fadeInLeft');
                toast.classList.add('animate__fadeOutLeft');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            });
        }

        // Función para manejar la vista rápida
        function handleQuickView(productId) {
            const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
            if (!productCard) return;

            const productName = productCard.getAttribute('data-name');
            const productPrice = parseFloat(productCard.getAttribute('data-price'));
            const productDescription = productCard.getAttribute('data-description');
            const productImage = productCard.getAttribute('data-image');
            const productCategory = productCard.querySelector('.product-category').textContent.trim();

            // Actualizar contenido del modal
            document.getElementById('modalProductTitle').textContent = productName;
            document.getElementById('modalProductCategory').textContent = productCategory;
            document.getElementById('modalProductPrice').textContent =
                `$${productPrice.toLocaleString('es-CO')}`;
            document.getElementById('modalProductDescription').textContent = productDescription;
            document.getElementById('modalProductImage').src = productImage;
            document.getElementById('modalProductImage').alt = productName;
            document.getElementById('productQuantity').value = 1;

            // Configurar botón de agregar al carrito
            const addToCartBtn = document.getElementById('modalAddToCart');
            addToCartBtn.setAttribute('data-product-id', productId);
        }

        // Eventos para filtros
        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
        sortFilter.addEventListener('change', filterProducts);

        // Evento para badges de categoría
        categoryBadges.forEach(badge => {
            badge.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                categoryFilter.value = category;
                filterProducts();
            });
        });

        // Evento para limpiar filtros
        clearFiltersBtn.addEventListener('click', function() {
            searchInput.value = '';
            categoryFilter.value = '';
            sortFilter.value = '';
            filterProducts();
        });

        // Eventos para botones de agregar al carrito
        document.querySelectorAll('.btn-add-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                addToCart(productId);
            });
        });

        // Eventos para botones de vista rápida
        document.querySelectorAll('.quick-view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                handleQuickView(productId);
            });
        });

        // Eventos para el modal de vista rápida
        quickViewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-product-id');
            handleQuickView(productId);
        });

        // Eventos para controles de cantidad
        document.getElementById('decreaseQuantity').addEventListener('click', function() {
            const quantityInput = document.getElementById('productQuantity');
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        document.getElementById('increaseQuantity').addEventListener('click', function() {
            const quantityInput = document.getElementById('productQuantity');
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < 10) {
                quantityInput.value = currentValue + 1;
            }
        });

        // Evento para agregar al carrito desde el modal
        document.getElementById('modalAddToCart').addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(document.getElementById('productQuantity').value);
            addToCart(productId, quantity);

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(quickViewModal);
            modal.hide();
        });

        // Evento para botones de favoritos
        document.querySelectorAll('.btn-wishlist').forEach(button => {
            button.addEventListener('click', function() {
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                if (this.classList.contains('active')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            });
        });

        // Evento para carrito flotante
        floatingCart.addEventListener('click', function() {
            window.location.href = 'carrito.php';
        });

        // Inicializar filtros al cargar
        filterProducts();

        // Animación al hacer scroll
        const animateOnScroll = function() {
            const sections = document.querySelectorAll('.category-section');

            sections.forEach(section => {
                const sectionTop = section.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;

                if (sectionTop < windowHeight * 0.75 && !section.classList.contains(
                        'animate__fadeInUp')) {
                    section.classList.add('animate__fadeInUp');
                }
            });
        };

        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Ejecutar una vez al cargar
    });
    </script>
</body>

</html>