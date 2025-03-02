<?php
// Iniciar la sesión para mostrar el nombre del usuario logueado si está autenticado
session_start();

// Depuración: Mostrar el contenido de la sesión para verificar si el usuario está logueado
error_log("Contenido de \$_SESSION en home.php: " . print_r($_SESSION, true));

// Verificar si el usuario está autenticado para mostrar su nombre
$username = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : null;

// Depuración: Mostrar el valor de $username
error_log("Valor de \$username: " . ($username ?: 'null'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabor Colombiano - Inicio</title>
    <!-- Bootstrap CSS: Incluye los estilos de Bootstrap para un diseño responsive y profesional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Montserrat: Añade tipografía elegante y profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Estilos personalizados: Define estilos específicos para la página home -->
    <style>
        /* ===== ESTILOS GLOBALES ===== */
        /* Configuración general del cuerpo de la página */
        body {
            background: linear-gradient(135deg, #FFC107, #FF5722, #4CAF50);
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        /* ===== HEADER Y NAVEGACIÓN ===== */
        /* Configuración del encabezado fijo con flexbox para alinear elementos horizontalmente */
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            display: flex !important; /* Fuerza la disposición en línea */
            justify-content: space-between !important; /* Distribuye los elementos */
            align-items: center !important; /* Alinea verticalmente */
            box-sizing: border-box;
        }
        
        /* Estilo del logo y su contenedor */
        .header-logo {
            flex-shrink: 0; /* Evita que el logo se comprima */
        }
        
        .header-logo img {
            max-width: 120px;
            border-radius: 10px;
            border: 3px solid #FF5722;
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
        }
        
        /* Enlaces de navegación */
        .nav-link {
            color: #4CAF50;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: block !important;
        }
        
        .nav-link:hover {
            color: #FF5722;
            background: rgba(76, 175, 80, 0.1);
        }
        
        /* Mensaje de bienvenida para el usuario logueado */
        .user-welcome {
            color: #FF5722;
            font-weight: bold;
            font-size: 1.2rem;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            margin-right: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, background-color 0.3s;
            white-space: nowrap; /* Evita que el texto se divida */
        }
        
        .user-welcome:hover {
            transform: scale(1.05);
            background-color: rgba(255, 255, 255, 1);
        }
        
        /* ===== BOTONES ===== */
        /* Botón de salir/iniciar sesión */
        .btn-salir {
            background-color: #FF5722;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            white-space: nowrap; /* Evita que el texto se divida */
            flex-shrink: 0; /* Evita que el botón se comprima */
        }
        
        .btn-salir:hover {
            background-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* ===== SECCIÓN HERO ===== */
        /* Sección principal de bienvenida */
        .hero {
            text-align: center;
            padding: 8rem 2rem 5rem; /* Espacio superior para evitar solapamiento con header */
            background: rgba(255, 255, 255, 0.8);
            margin: 0 auto;
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .hero h2 {
            color: #FF5722;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero p {
            color: #4CAF50;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        /* Botón de información */
        .btn-info {
            background-color: #FFC107;
            color: #333;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .btn-info:hover {
            background-color: #FF5722;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* ===== FOOTER ===== */
        /* Pie de página */
        footer {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 0.9rem;
            margin-top: 2rem;
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
            
            .user-welcome {
                order: 2;
                margin: 0.5rem 0;
            }
            
            .btn-salir {
                order: 4;
                margin: 0.5rem 0;
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
    </style>
</head>
<body>
    <!-- Header con logo, navegación y nombre del usuario (si está logueado) -->
    <header>
        <!-- Logo de la página con enlace a la página principal -->
        <div class="header-logo">
            <a href="home.php">
                <img src="palenque.jpeg" alt="San Basilio de Palenque">
            </a>
        </div>
        
        <!-- Navegación principal con enlaces a diferentes secciones -->
        <nav class="nav-links">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
            </ul>
        </nav>
        
        <!-- Mensaje de bienvenida y botón de salir/iniciar sesión según estado de autenticación -->
        <?php if ($username): ?>
            <span class="user-welcome">¡Hola, <?php echo $username; ?>!</span>
            <a href="index.php"><button class="btn-salir">Salir</button></a>
        <?php else: ?>
            <a href="login.php"><button class="btn-salir">Iniciar Sesión</button></a>
        <?php endif; ?>
    </header>

    <!-- Sección hero con mensaje de bienvenida y botón de información -->
    <section class="hero">
        <h2>¡Bienvenido a Sabor Colombiano!</h2>
        <p>Explora y descubre la esencia de nuestra tierra: alegría, color y tradición.</p>
        <a href="#" class="btn-info">Más Información</a>
    </section>

    <!-- Footer con información de copyright -->
    <footer>
        <p>© 2025 Sabor Colombiano - Todos los derechos reservados.</p>
    </footer>

    <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap para interacciones dinámicas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>