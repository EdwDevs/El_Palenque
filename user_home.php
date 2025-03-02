<?php
// Iniciar la sesión para acceder a los datos del usuario
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión activa, redirigir al login
    header("Location: index.php");
    exit();
}

// Verificar si el usuario tiene rol de usuario regular (no admin)
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    // Si es admin, redirigir al panel de administración
    header("Location: admin_home.php");
    exit();
}

// Almacenar el nombre del usuario en una variable con seguridad
$username = htmlspecialchars($_SESSION['usuario']); // Escapar caracteres para prevenir XSS
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de usuario de Sabor Colombiano - Accede a servicios exclusivos">
    <title>Panel de Usuario - Sabor Colombiano</title>
    
    <!-- Bootstrap CSS: Framework para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Montserrat: Tipografía principal del sitio -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome: Biblioteca de iconos para mejorar la interfaz -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper CSS: Para el carrusel deslizable -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    
    <!-- Estilos personalizados: Define la apariencia específica de la aplicación -->
    <style>
        /* Variables CSS para mantener consistencia en colores y valores */
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
        
        /* ===== ESTILOS GLOBALES ===== */
        /* Definición del fondo, fuente y estructura básica del documento */
        body {
            /* Fondo con degradado de colores que representan la bandera colombiana */
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: var(--color-text);
            margin: 0;
            padding: 0;
            position: relative;
            padding-bottom: 60px; /* Espacio para el footer */
        }

        /* ===== HEADER Y NAVEGACIÓN ===== */
        /* Configuración del encabezado fijo en la parte superior */
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: var(--box-shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            display: flex !important; /* Fuerza la disposición en línea */
            justify-content: space-between !important; /* Distribuye los elementos */
            align-items: center !important; /* Alinea verticalmente */
            box-sizing: border-box;
            transition: var(--transition-normal);
        }
        
        /* Efecto de scroll para el header */
        header.scrolled {
            padding: 0.5rem 2rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Estilo del logo y su contenedor */
        .header-logo {
            flex-shrink: 0; /* Evita que el logo se comprima */
        }

        .header-logo img {
            max-width: 120px;
            border-radius: var(--border-radius);
            border: 3px solid var(--color-primary);
            transition: transform 0.3s ease;
            display: block;
        }

        .header-logo img:hover {
            transform: scale(1.05);
        }

        /* Contenedor de los enlaces de navegación */
        .nav-links {
            flex-grow: 1; /* Ocupa el espacio disponible */
            display: flex !important; /* Fuerza la disposición en línea */
            justify-content: center !important; /* Centra los elementos */
        }

        /* Lista de navegación */
        .navbar-nav {
            display: flex !important; /* Fuerza la disposición en línea */
            flex-direction: row !important; /* Asegura que sea horizontal */
            gap: 2rem !important; /* Espacio entre elementos */
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        /* Elementos individuales de la navegación */
        .nav-item {
            margin: 0;
            padding: 0;
            display: block !important;
            position: relative;
        }

        /* Enlaces de navegación */
        .nav-link {
            color: var(--color-secondary);
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: var(--transition-normal);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex !important;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            color: var(--color-primary);
            background: rgba(76, 175, 80, 0.1);
            transform: translateY(-2px);
        }
        
        /* Indicador de enlace activo */
        .nav-link.active {
            color: var(--color-primary);
            background: rgba(255, 87, 34, 0.1);
            position: relative;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--color-primary);
            border-radius: 3px;
        }

        /* ===== BOTONES ===== */
        /* Botón de salir */
        .btn-auth {
            background-color: var(--color-primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
            cursor: pointer;
            white-space: nowrap; /* Evita que el texto se divida */
            flex-shrink: 0; /* Evita que el botón se comprima */
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-auth:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Mensaje de bienvenida al usuario */
        .user-welcome {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-primary);
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            margin-right: 1rem;
            transition: var(--transition-normal);
        }
        
        .user-welcome:hover {
            background-color: var(--color-hover);
            transform: translateY(-2px);
        }

        /* Botón de información */
        .btn-info {
            background-color: var(--color-accent);
            color: var(--color-text);
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-normal);
        }

        .btn-info:hover {
            background-color: var(--color-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Botón de servicio */
        .btn-service {
            background-color: var(--color-secondary);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition-normal);
            margin-top: 1rem;
            border: none;
            cursor: pointer;
        }

        .btn-service:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* ===== SECCIÓN HERO ===== */
        /* Sección principal de bienvenida */
        .hero {
            text-align: center;
            padding: 10rem 2rem 5rem; /* Espacio superior para evitar solapamiento con header */
            background: rgba(255, 255, 255, 0.9);
            margin: 0 auto;
            max-width: 800px;
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            animation: fadeIn 1s ease-in-out;
        }
        
        /* Animación de aparición suave */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero h2 {
            color: var(--color-primary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        /* Línea decorativa debajo del título */
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
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Contenedor de botones en el hero */
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        /* ===== SECCIÓN DE SERVICIOS ===== */
        .features {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 2rem auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .features h3 {
            color: var(--color-primary);
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 0.5rem;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .features h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--color-primary);
            border-radius: 3px;
        }
        
        /* Estilos para el carrusel Swiper */
        .swiper {
            width: 100%;
            padding-bottom: 50px; /* Espacio para la paginación */
        }
        
        .swiper-slide {
            height: auto; /* Permite que las tarjetas tengan altura variable */
            display: flex;
        }
        
        /* Estilos para los botones de navegación del carrusel */
        .swiper-button-next, .swiper-button-prev {
            color: var(--color-primary);
            background: rgba(255, 255, 255, 0.8);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 18px;
            font-weight: bold;
        }
        
        .swiper-button-next:hover, .swiper-button-prev:hover {
            background: var(--color-light);
            transform: scale(1.1);
        }
        
        /* Estilos para la paginación del carrusel */
        .swiper-pagination-bullet {
            background: var(--color-secondary);
            opacity: 0.5;
        }
        
        .swiper-pagination-bullet-active {
            background: var(--color-primary);
            opacity: 1;
        }
        
        /* Tarjetas de características */
        .feature-card {
            background: var(--color-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: var(--transition-normal);
            text-align: center;
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--color-accent);
            margin-bottom: 1rem;
        }
        
        .feature-title {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .feature-description {
            color: var(--color-text);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            flex-grow: 1; /* Hace que el texto ocupe el espacio disponible */
        }
        
        .feature-button-container {
            margin-top: auto; /* Empuja el botón hacia abajo */
        }
        
        /* Estilos para formularios */
        .form-container {
            background: var(--color-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }
        
        .form-label {
            color: var(--color-primary);
            font-weight: 600;
        }
        
        .form-control {
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            padding: 0.75rem;
            transition: var(--transition-normal);
        }
        
        .form-control:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        }
        
        /* Estilos para el perfil */
        .profile-options {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        /* ===== FOOTER ===== */
        /* Pie de página */
        footer {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            color: var(--color-text);
            font-size: 0.9rem;
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(255, 193, 7, 0.3);
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
            gap: 1rem;
        }
        
        .footer-link {
            color: var(--color-secondary);
            text-decoration: none;
            transition: var(--transition-normal);
        }
        
        .footer-link:hover {
            color: var(--color-primary);
        }
        
        .social-icons {
            display: flex;
            gap: 1rem;
        }
        
        .social-icon {
            color: var(--color-secondary);
            font-size: 1.2rem;
            transition: var(--transition-normal);
        }
        
        .social-icon:hover {
            color: var(--color-primary);
            transform: translateY(-2px);
        }

        /* ===== RESPONSIVE ===== */
        /* Ajustes para dispositivos móviles */
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
                gap: 1rem;
            }
            
            .hero {
                padding: 8rem 1.5rem 3rem;
                margin: 0 1rem;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .features {
                padding: 2rem 1rem;
                margin: 1rem;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links, .social-icons {
                justify-content: center;
            }
            
            /* Ajustes para el carrusel en móviles */
            .swiper-button-next, .swiper-button-prev {
                width: 30px;
                height: 30px;
            }
            
            .swiper-button-next:after, .swiper-button-prev:after {
                font-size: 14px;
            }
        }

        /* ===== CORRECCIONES PARA BOOTSTRAP ===== */
        /* Estas reglas sobrescriben los estilos de Bootstrap que podrían interferir */
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
        
        /* ===== ACCESIBILIDAD ===== */
        /* Mejoras para accesibilidad */
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
        
        /* Enfoque visible para navegación por teclado */
        a:focus, button:focus {
            outline: 3px solid var(--color-accent);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- HEADER: Contiene el logo, navegación y botón de salir -->
    <header id="main-header">
        <div class="header-logo">
            <a href="index.php" title="Página de inicio">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>
        
        <nav class="nav-links" aria-label="Navegación principal">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="user_home.php" aria-current="page">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#servicios">
                        <i class="fas fa-utensils"></i> Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#perfil">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contacto">
                        <i class="fas fa-envelope"></i> Contacto
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="auth-container">
            <span class="user-welcome">
                <i class="fas fa-user"></i> Hola, <?php echo $username; ?>
            </span>
            <a href="logout.php" title="Cerrar sesión">
                <button class="btn-auth">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </button>
            </a>
        </div>
    </header>

    <!-- SECCIÓN HERO: Contiene el mensaje de bienvenida -->
    <section class="hero">
        <h2>¡Bienvenido a tu Panel de Usuario!</h2>
        <p>Explora y descubre todos los servicios exclusivos que tenemos para ti. Disfruta de la esencia de nuestra tierra: alegría, color y tradición.</p>
        <div class="hero-buttons">
            <a href="#servicios" class="btn-info">
                <i class="fas fa-arrow-down"></i> Ver Servicios
            </a>
        </div>
    </section>

    <!-- SECCIÓN DE SERVICIOS: Muestra los servicios disponibles para el usuario -->
    <section class="features" id="servicios">
        <h3>Servicios Exclusivos</h3>
        
        <!-- Swiper: Carrusel deslizable para las tarjetas de servicios -->
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <!-- Slide 1: Tradiciones -->
                <div class="swiper-slide">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4 class="feature-title">Tradiciones</h4>
                        <p class="feature-description">Accede a nuestra colección exclusiva de recetas tradicionales colombianas, con instrucciones paso a paso y consejos de nuestros chefs.</p>
                        <div class="feature-button-container">
                            <a href="tradiciones.php" class="btn-service">
                                <i class="fas fa-arrow-right"></i> Conoce Más
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2: Productos -->
                <div class="swiper-slide">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4 class="feature-title">Productos</h4>
                        <p class="feature-description">Mantente informado sobre nuestros próximos eventos culturales, talleres de cocina y celebraciones tradicionales.</p>
                        <div class="feature-button-container">
                            <a href="productos.php" class="btn-service">
                                <i class="fas fa-arrow-right"></i> Ver Productos
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3: Historias de la Comunidad -->
                <div class="swiper-slide">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h4 class="feature-title">Historias de la Comunidad</h4>
                        <p class="feature-description">Actualiza tus datos personales, cambia tu contraseña y personaliza tus preferencias en nuestra plataforma.</p>
                        <div class="feature-button-container">
                            <a href="Historias_comunidad.php" class="btn-service">
                                <i class="fas fa-arrow-right"></i> Cuentanos tu Historia
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Controles de navegación del carrusel -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    
    <!-- SECCIÓN DE PERFIL: Permite al usuario gestionar su información -->
    <section class="features" id="perfil">
        <h3>Mi Perfil</h3>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-user-cog"></i>
            </div>
            <h4 class="feature-title">Gestiona tu información personal</h4>
            <p class="feature-description">Actualiza tus datos, cambia tu contraseña y personaliza tus preferencias en nuestra plataforma.</p>
            
            <div class="profile-options">
                <a href="editar_perfil.php" class="btn-info">
                    <i class="fas fa-user-edit"></i> Editar Datos
                </a>
                <a href="cambiar_contrasena.php" class="btn-info" style="background-color: var(--color-secondary); color: white;">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </a>
            </div>
        </div>
    </section>
    
    <!-- SECCIÓN DE CONTACTO: Información de contacto -->
    <section class="features" id="contacto" style="margin-bottom: 5rem;">
        <h3>Contáctanos</h3>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h4 class="feature-title">¿Tienes alguna pregunta o sugerencia?</h4>
            <p class="feature-description">Estamos aquí para ayudarte. Envíanos un mensaje y te responderemos lo antes posible.</p>
            
            <div class="form-container">
                <form id="contactForm">
                    <div class="mb-3">
                        <label for="asunto" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="asunto" required>
                    </div>
                    <div class="mb-3">
                        <label for="mensaje" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="mensaje" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn-service" style="margin-top: 1rem;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje
                    </button>
                </form>
            </div>
            
            <div class="d-flex justify-content-center gap-4 mt-4">
                <div class="text-center">
                    <i class="fas fa-phone fa-2x mb-2" style="color: var(--color-secondary);"></i>
                    <p>+57 123 456 7890</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-envelope fa-2x mb-2" style="color: var(--color-secondary);"></i>
                    <p>info@saborcolombiano.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER: Contiene información de copyright y enlaces adicionales -->
    <footer>
        <div class="footer-content">
            <div class="copyright">
                <p>© 2025 Sabor Colombiano - Todos los derechos reservados.</p>
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

    <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Swiper JS: Para el carrusel deslizable -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- Script personalizado para mejorar la experiencia de usuario -->
    <script>
        // Cuando el documento esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Efecto de scroll para el header
            window.addEventListener('scroll', function() {
                const header = document.getElementById('main-header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            
            // Navegación suave al hacer clic en los enlaces
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
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                // Responsive breakpoints
                breakpoints: {
                    // Cuando el ancho de la ventana es >= 768px
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    // Cuando el ancho de la ventana es >= 1024px
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                },
                // Accesibilidad
                a11y: {
                    prevSlideMessage: 'Slide anterior',
                    nextSlideMessage: 'Siguiente slide',
                    firstSlideMessage: 'Este es el primer slide',
                    lastSlideMessage: 'Este es el último slide',
                    paginationBulletMessage: 'Ir al slide {{index}}'
                }
            });
            
            // Manejar el envío del formulario de contacto
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Aquí normalmente enviarías los datos mediante AJAX
                    // Para este ejemplo, solo mostraremos un mensaje de éxito
                    alert('¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.');
                    this.reset();
                });
            }
        });
    </script>
</body>
</html>