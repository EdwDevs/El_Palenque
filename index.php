<?php
session_start();

// Definir $isLoggedIn basado en la sesión
$isLoggedIn = isset($_SESSION['usuario']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['usuario']) : '';
$dashboardLink = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') ? "admin_home.php" : "user_home.php";

include('db.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sabor Colombiano - Descubre la esencia de la gastronomía y cultura colombiana">
    <title>Sabor Colombiano - Inicio</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <!-- AOS - Animate On Scroll Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <style>
    /* Variables CSS */
    :root {
        --color-primary: #FF5722;
        --color-secondary: #4CAF50;
        --color-accent: #FFC107;
        --color-text: #333333;
        --color-light: #FFFFFF;
        --color-hover: #FFF3E0;
        --color-bg-light: rgba(255, 255, 255, 0.95);
        --color-bg-dark: rgba(51, 51, 51, 0.05);
        --border-radius-sm: 6px;
        --border-radius: 10px;
        --border-radius-lg: 20px;
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --box-shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.12);
        --transition-fast: all 0.2s ease;
        --transition-normal: all 0.3s ease;
        --transition-slow: all 0.5s ease;
        --spacing-xs: 0.5rem;
        --spacing-sm: 1rem;
        --spacing-md: 2rem;
        --spacing-lg: 4rem;
    }

    /* Estilos globales */
    body {
        background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
        min-height: 100vh;
        font-family: 'Montserrat', sans-serif;
        color: var(--color-text);
        margin: 0;
        padding: 0;
        position: relative;
        padding-bottom: 70px;
        overflow-x: hidden;
    }

    /* Header y navegación */
    header {
        background: var(--color-bg-light);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        padding: 1rem 2rem;
        box-shadow: var(--box-shadow);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 1000;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        box-sizing: border-box;
        transition: var(--transition-normal);
    }

    header.scrolled {
        padding: 0.5rem 2rem;
        background: var(--color-bg-light);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .header-logo {
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }

    .header-logo img {
        max-width: 120px;
        border-radius: var(--border-radius);
        border: 3px solid var(--color-primary);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: block;
        box-shadow: 0 5px 15px rgba(255, 87, 34, 0.2);
    }

    .header-logo img:hover {
        transform: scale(1.05) rotate(2deg);
    }

    .nav-links {
        flex-grow: 1;
        display: flex !important;
        justify-content: center !important;
    }

    .navbar-nav {
        display: flex !important;
        flex-direction: row !important;
        gap: 2rem !important;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }

    .nav-item {
        margin: 0;
        padding: 0;
        display: block !important;
        position: relative;
    }

    .nav-link {
        color: var(--color-secondary);
        font-weight: 600;
        font-size: 1.1rem;
        text-decoration: none;
        transition: var(--transition-normal);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius-sm);
        display: flex !important;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
    }

    .nav-link:before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--color-primary);
        transform: translateX(-100%);
        transition: var(--transition-normal);
    }

    .nav-link:hover {
        color: var(--color-primary);
        background: rgba(76, 175, 80, 0.08);
        transform: translateY(-2px);
    }

    .nav-link:hover:before {
        transform: translateX(0);
    }

    .nav-link.active {
        color: var(--color-primary);
        background: rgba(255, 87, 34, 0.08);
        position: relative;
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--color-primary);
        border-radius: 3px 3px 0 0;
    }

    /* Botones */
    .btn-auth {
        background-color: var(--color-primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition-normal);
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(255, 87, 34, 0.3);
    }

    .btn-auth:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 150%;
        height: 150%;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: var(--transition-fast);
    }

    .btn-auth:hover {
        background-color: var(--color-secondary);
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
    }

    .btn-auth:hover:after {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    .btn-auth:active {
        transform: translateY(-1px);
    }

    .user-welcome {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--color-primary);
        font-weight: 600;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        margin-right: 1rem;
        transition: var(--transition-normal);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .user-welcome:hover {
        background-color: var(--color-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .btn-info {
        background-color: var(--color-accent);
        color: var(--color-text);
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition-normal);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
        border: none;
    }

    .btn-info:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 150%;
        height: 150%;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: var(--transition-fast);
    }

    .btn-info:hover {
        background-color: var(--color-primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(255, 87, 34, 0.3);
    }

    .btn-info:hover:after {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    .btn-service {
        background-color: var(--color-secondary);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: var(--transition-normal);
        margin-top: 1.5rem;
        border: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
    }

    .btn-service:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 150%;
        height: 150%;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: var(--transition-fast);
    }

    .btn-service:hover {
        background-color: var(--color-primary);
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(255, 87, 34, 0.3);
    }

    .btn-service:hover:after {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    /* Iconos de acción */
    .action-icon {
        font-size: 1.5rem;
        color: var(--color-primary);
        margin-right: 1rem;
        transition: var(--transition-normal);
        position: relative;
    }

    .action-icon:hover {
        color: var(--color-secondary);
        transform: translateY(-2px);
    }

    /* Sección Hero */
    .hero {
        text-align: center;
        padding: 10rem 2rem 5rem;
        background: var(--color-bg-light);
        margin: 0 auto;
        max-width: 900px;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--box-shadow);
        position: relative;
        overflow: hidden;
    }

    .hero:before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 100px;
        height: 100px;
        background: var(--color-accent);
        opacity: 0.1;
        border-radius: 50%;
    }

    .hero:after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: -30px;
        width: 80px;
        height: 80px;
        background: var(--color-primary);
        opacity: 0.1;
        border-radius: 50%;
    }

    .hero h2 {
        color: var(--color-primary);
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.8rem;
    }

    .hero h2::after {
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

    .hero p {
        color: var(--color-secondary);
        font-size: 1.2rem;
        margin-bottom: 2.5rem;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }

    .hero-buttons {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    /* Sección de características */
    .features {
        padding: 5rem 2rem;
        max-width: 1200px;
        margin: 3rem auto;
        background: var(--color-bg-light);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--box-shadow);
        position: relative;
        overflow: hidden;
    }

    .features:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
    }

    .features h3 {
        color: var(--color-primary);
        text-align: center;
        margin-bottom: 3rem;
        font-weight: 700;
        font-size: 2rem;
        position: relative;
        display: inline-block;
        padding-bottom: 0.8rem;
    }

    .features h3:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--color-accent);
        border-radius: 3px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .swiper {
        width: 100%;
        padding-bottom: 60px;
        overflow: visible;
    }

    .swiper-slide {
        height: auto;
        display: flex;
        transition: var(--transition-normal);
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: var(--color-primary);
        background: rgba(255, 255, 255, 0.9);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: var(--transition-normal);
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 18px;
        font-weight: bold;
    }

    .swiper-button-next:hover,
    .swiper-button-prev:hover {
        background: var(--color-light);
        transform: scale(1.1);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .swiper-pagination-bullet {
        background: var(--color-secondary);
        opacity: 0.5;
        transition: var(--transition-normal);
    }

    .swiper-pagination-bullet-active {
        background: var(--color-primary);
        opacity: 1;
        transform: scale(1.2);
    }

    .feature-card {
        background: var(--color-light);
        padding: 2.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition-normal);
        text-align: center;
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
        position: relative;
        overflow: hidden;
        border-top: 4px solid transparent;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--box-shadow-hover);
        border-top: 4px solid var(--color-accent);
    }

    .feature-icon {
        font-size: 3rem;
        color: var(--color-accent);
        margin-bottom: 1.5rem;
        transition: var(--transition-normal);
        display: inline-block;
    }

    .feature-card:hover .feature-icon {
        transform: scale(1.1) rotate(5deg);
        color: var(--color-primary);
    }

    .feature-title {
        color: var(--color-primary);
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }

    .feature-description {
        color: var(--color-text);
        font-size: 1rem;
        margin-bottom: 1.5rem;
        flex-grow: 1;
        line-height: 1.6;
    }

    .feature-button-container {
        margin-top: auto;
    }

    .tradition-list {
        text-align: left;
        margin: 1.5rem 0;
        padding-left: 0;
        list-style-position: inside;
    }

    .tradition-list li {
        margin-bottom: 0.8rem;
        position: relative;
        padding-left: 1.8rem;
        list-style-type: none;
        transition: var(--transition-normal);
    }

    .tradition-list li:before {
        content: "•";
        color: var(--color-primary);
        font-weight: bold;
        position: absolute;
        left: 0;
        font-size: 1.2rem;
        transition: var(--transition-normal);
    }

    .tradition-list li:hover {
        transform: translateX(5px);
    }

    .tradition-list li:hover:before {
        color: var(--color-accent);
    }

    /* Footer */
    footer {
        text-align: center;
        padding: 1.5rem;
        background: var(--color-bg-light);
        color: var(--color-text);
        font-size: 0.9rem;
        position: absolute;
        bottom: 0;
        width: 100%;
        border-top: 1px solid rgba(255, 193, 7, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-links {
        display: flex;
        gap: 1.5rem;
    }

    .footer-link {
        color: var(--color-secondary);
        text-decoration: none;
        transition: var(--transition-normal);
        position: relative;
    }

    .footer-link:after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 100%;
        height: 1px;
        background: var(--color-primary);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.3s ease;
    }

    .footer-link:hover {
        color: var(--color-primary);
    }

    .footer-link:hover:after {
        transform: scaleX(1);
        transform-origin: left;
    }

    .social-icons {
        display: flex;
        gap: 1.2rem;
    }

    .social-icon {
        color: var(--color-secondary);
        font-size: 1.3rem;
        transition: var(--transition-normal);
        display: inline-block;
    }

    .social-icon:hover {
        color: var(--color-primary);
        transform: translateY(-3px) rotate(5deg);
    }

    /* Notificación */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: var(--border-radius);
        color: var(--color-light);
        z-index: 1000;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        display: none;
        animation: slideIn 0.5s ease forwards;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .success {
        background-color: var(--color-secondary);
        border-left: 5px solid #388E3C;
    }

    /* Animaciones */
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

    @keyframes floatUp {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }

        100% {
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 1s ease-in-out;
    }

    .animate-float {
        animation: floatUp 3s ease-in-out infinite;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .hero h2 {
            font-size: 2.2rem;
        }

        .features h3 {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 768px) {
        header {
            flex-wrap: wrap;
            padding: 1rem;
            gap: 1rem;
        }

        .nav-links {
            order: 3;
            width: 100%;
            justify-content: center;
        }

        .navbar-nav {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.8rem;
        }

        .hero {
            padding: 8rem 1.5rem 3rem;
            margin: 0 1rem;
        }

        .hero h2 {
            font-size: 1.8rem;
        }

        .hero p {
            font-size: 1rem;
        }

        .features {
            padding: 3rem 1.5rem;
            margin: 2rem 1rem;
        }

        .footer-content {
            flex-direction: column;
            text-align: center;
            gap: 1.5rem;
        }

        .footer-links,
        .social-icons {
            justify-content: center;
        }

        .swiper-button-next,
        .swiper-button-prev {
            width: 36px;
            height: 36px;
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 14px;
        }
    }

    @media (max-width: 576px) {
        .hero-buttons {
            flex-direction: column;
            gap: 1rem;
        }

        .btn-info,
        .btn-service {
            width: 100%;
            justify-content: center;
        }

        .feature-card {
            padding: 1.5rem;
        }
    }

    /* Correcciones para Bootstrap */
    @media all {
        .navbar-nav {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
        }

        .nav-item {
            display: block !important;
        }

        .navbar-nav .nav-link {
            padding: 0.5rem 1rem !important;
        }
    }

    /* Accesibilidad */
    .visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    a:focus,
    button:focus {
        outline: 3px solid var(--color-accent);
        outline-offset: 2px;
    }

    /* Mejoras de accesibilidad */
    .skip-link {
        position: absolute;
        top: -40px;
        left: 0;
        background: var(--color-primary);
        color: white;
        padding: 8px;
        z-index: 1001;
        transition: top 0.3s;
    }

    .skip-link:focus {
        top: 0;
    }
    </style>
</head>

<body>
    <!-- Skip link para accesibilidad -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <!-- HEADER -->
    <header id="main-header">
        <div class="header-logo">
            <a href="index.php" title="Página de inicio">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>

        <nav class="nav-links" aria-label="Navegación principal">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php" aria-current="page">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#servicios">
                        <i class="fas fa-utensils"></i> Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contacto">
                        <i class="fas fa-envelope"></i> Contacto
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $dashboardLink; ?>">
                        <i class="fas fa-tachometer-alt"></i> Mi Panel
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <?php if ($isLoggedIn): ?>
        <span class="user-welcome">
            <i class="fas fa-user"></i> Hola, <?php echo $username; ?>
        </span>
        <a href="carrito.php" class="action-icon" title="Ver carrito de compras" aria-label="Carrito de compras">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <a href="ver_pedido.php" class="action-icon" title="Ver mis pedidos" aria-label="Ver pedidos">
            <i class="fas fa-list"></i>
        </a>
        <a href="logout.php" title="Cerrar sesión">
            <button class="btn-auth">
                <i class="fas fa-sign-out-alt"></i> Salir
            </button>
        </a>
        <?php else: ?>
        <a href="login.php" title="Iniciar sesión">
            <button class="btn-auth">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </a>
        <?php endif; ?>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main id="main-content">
        <!-- SECCIÓN HERO -->
        <section class="hero" data-aos="fade-up">
            <h2>¡Bienvenido a Sabor Colombiano!</h2>
            <p>Explora y descubre la esencia de nuestra tierra: alegría, color y tradición. Sumérgete en una experiencia
                gastronómica única que celebra la diversidad cultural de Colombia.</p>

            <div class="hero-buttons">
                <a href="#servicios" class="btn-info" data-aos="fade-right" data-aos-delay="200">
                    <i class="fas fa-info-circle"></i> Más Información
                </a>
                <?php if (!$isLoggedIn): ?>
                <a href="register.php" class="btn-info" style="background-color: var(--color-secondary); color: white;"
                    data-aos="fade-left" data-aos-delay="300">
                    <i class="fas fa-user-plus"></i> Registrarse
                </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- SECCIÓN DE CARACTERÍSTICAS -->
        <section class="features" id="servicios" data-aos="fade-up">
            <h3>Nuestros Servicios</h3>
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <!-- Slide 1: Tradiciones -->
                    <div class="swiper-slide" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-card">
                            <div class="feature-icon animate-float">
                                <i class="fas fa-music"></i>
                            </div>
                            <h4 class="feature-title">Tradiciones</h4>
                            <p class="feature-description">Descubre las ricas tradiciones culturales de San Basilio de
                                Palenque, el primer pueblo libre de América:</p>
                            <ul class="tradition-list">
                                <li>Música y danzas ancestrales</li>
                                <li>Rituales y ceremonias tradicionales</li>
                                <li>Medicina ancestral y saberes</li>
                            </ul>
                            <div class="feature-button-container">
                                <a href="tradiciones.php" class="btn-service">
                                    <i class="fas fa-arrow-right"></i> Explorar Tradiciones
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2: Productos -->
                    <div class="swiper-slide" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-card">
                            <div class="feature-icon animate-float">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                            <h4 class="feature-title">Productos Artesanales</h4>
                            <p class="feature-description">Explora nuestra selección de productos artesanales
                                colombianos, elaborados con técnicas tradicionales y amor por nuestra cultura.</p>
                            <div class="feature-button-container">
                                <a href="productos_compra.php" class="btn-service">
                                    <i class="fas fa-arrow-right"></i> Ver Productos
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3: Historias -->
                    <div class="swiper-slide" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-card">
                            <div class="feature-icon animate-float">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="feature-title">Historias de la Comunidad</h4>
                            <p class="feature-description">Conoce las historias inspiradoras de nuestra comunidad y cómo
                                mantenemos vivas nuestras tradiciones a través de generaciones.</p>
                            <div class="feature-button-container">
                                <a href="historias_comunidad.php" class="btn-service">
                                    <i class="fas fa-arrow-right"></i> Ver Historias
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <!-- SECCIÓN DE CONTACTO -->
        <section class="features" id="contacto" data-aos="fade-up">
            <h3>Contáctanos</h3>
            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4 class="feature-title">Ubicación</h4>
                    <p class="feature-description">Calle Principal #123<br>Bogotá, Colombia</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h4 class="feature-title">Teléfono</h4>
                    <p class="feature-description">+57 123 456 7890</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4 class="feature-title">Correo Electrónico</h4>
                    <p class="feature-description">info@saborcolombiano.com</p>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="copyright">
                <p>&copy; 2025 Sabor Colombiano - Todos los derechos reservados</p>
            </div>
            <div class="footer-links">
                <a href="#" class="footer-link">Términos y Condiciones</a>
                <a href="#" class="footer-link">Política de Privacidad</a>
            </div>
            <div class="social-icons">
                <a href="#" class="social-icon" title="Facebook" aria-label="Visita nuestra página de Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="social-icon" title="Instagram" aria-label="Síguenos en Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-icon" title="Twitter" aria-label="Síguenos en Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- Notificación -->
    <?php if (isset($_GET['mensaje'])): ?>
    <div id="notification" class="notification success">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicialización de AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Efecto de scroll para el header
        window.addEventListener('scroll', function() {
            const header = document.getElementById('main-header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Inicialización del carrusel Swiper
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            },
            a11y: {
                prevSlideMessage: 'Slide anterior',
                nextSlideMessage: 'Siguiente slide',
                firstSlideMessage: 'Este es el primer slide',
                lastSlideMessage: 'Este es el último slide',
                paginationBulletMessage: 'Ir al slide {{index}}'
            }
        });

        // Notificación
        <?php if (isset($_GET['mensaje'])): ?>
        const notification = document.getElementById('notification');
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.style.display = 'none';
                notification.style.opacity = '1';
            }, 300);
        }, 3000);
        <?php endif; ?>

        // Limpiar localStorage si clear_cart=1 está en la URL
        <?php if (isset($_GET['clear_cart']) && $_GET['clear_cart'] == '1'): ?>
        localStorage.removeItem('cart');
        <?php endif; ?>
    });
    </script>
</body>

</html>