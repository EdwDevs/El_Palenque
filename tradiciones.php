<?php
// Iniciar o reanudar la sesión
session_start();

// Validar la sesión sin mostrar información
$sesion_activa = (session_status() == PHP_SESSION_ACTIVE);
$sesion_con_datos = !empty($_SESSION);
$usuario_logueado = isset($_SESSION['usuario']);
$es_admin = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

// Puedes usar estas variables para lógica condicional en tu página
// pero no se mostrará ninguna información de depuración

// Si necesitas verificar que todo funciona, puedes usar este comentario
/*
$info_debug = [
    'sesion_activa' => $sesion_activa,
    'sesion_con_datos' => $sesion_con_datos,
    'usuario_logueado' => $usuario_logueado,
    'es_admin' => $es_admin,
    'session_data' => $_SESSION
];
*/

// No hay salida visible en la página
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="San Basilio de Palenque, tradiciones, música, Lumbalú, medicina tradicional, patrimonio cultural">
    <meta name="author" content="San Basilio de Palenque">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Preconectar a dominios externos -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://images.unsplash.com">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS para animaciones -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="styles.css">
    
    <style>
        :root {
            --color-primary: #4CAF50;
            --color-secondary: #FF5722;
            --color-accent: #e74c3c;
            --color-light: #ecf0f1;
            --color-dark: #34495e;
            --color-text: #333;
            --color-bg: #f9f9f9;
            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 6px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-full: 9999px;
        }

        body {
            font-family: var(--font-main);
            color: var(--color-text);
            background-color: var(--color-bg);
            padding-top: 100px;
            scroll-behavior: smooth;
            line-height: 1.6;
        }

        /* Header modernizado */
        .fixed-top {
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .navbar {
            padding: 0.5rem 1.5rem;
        }

        .header-logo img {
            border-radius: 50%;
            border: 3px solid var(--color-primary);
            transition: transform 0.3s ease;
            object-fit: cover;
        }

        .header-logo img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            color: #495057;
            margin: 0 5px;
            padding: 10px 16px;
            border-radius: var(--radius-full);
            transition: var(--transition);
            font-family: var(--font-heading);
            position: relative;
        }

        .nav-link:hover {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--color-primary);
        }

        .nav-link.active {
            color: white;
            background-color: var(--color-primary);
        }

        .admin-link {
            color: #28a745;
            border: 1px dashed #28a745;
        }

        .admin-link:hover {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        /* Autenticación modernizada */
        .auth-section {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-left: auto;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            background-color: rgba(76, 175, 80, 0.1);
            padding: 8px 16px;
            border-radius: var(--radius-full);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .user-welcome:hover {
            background-color: rgba(76, 175, 80, 0.15);
            transform: translateY(-2px);
        }

        .user-name {
            font-weight: 600;
            color: var(--color-primary);
            margin-right: 15px;
            display: flex;
            align-items: center;
        }

        .user-name i {
            font-size: 1.2rem;
            margin-right: 8px;
            color: var(--color-primary);
        }

        .logout-btn {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--color-accent);
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: var(--color-accent);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .logout-btn i {
            margin-right: 5px;
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .login-btn, .register-btn {
            padding: 8px 16px;
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            font-weight: 500;
        }

        .login-btn {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }

        .login-btn:hover {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .register-btn {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }

        .register-btn:hover {
            background-color: #0069d9;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .login-btn i, .register-btn i {
            margin-right: 5px;
        }

        /* Banner principal modernizado */
        .tradition-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('https://images.unsplash.com/photo-1518019671582-55004f1bc9ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            text-align: center;
            margin-bottom: 60px;
            position: relative;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .tradition-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.4), rgba(255, 87, 34, 0.4));
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            z-index: 0;
        }

        .tradition-banner .container {
            position: relative;
            z-index: 1;
        }

        .tradition-banner h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 700;
            font-family: var(--font-heading);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .tradition-banner p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Secciones modernizadas */
        .tradition-section {
            padding: 70px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .tradition-section:last-child {
            border-bottom: none;
        }

        .tradition-section h2 {
            color: var(--color-primary);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 2.2rem;
        }

        .tradition-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--color-primary), var(--color-secondary));
            border-radius: 2px;
        }

        .tradition-section h3 {
            color: var(--color-secondary);
            margin: 30px 0 20px;
            font-weight: 600;
            font-family: var(--font-heading);
            font-size: 1.6rem;
        }

        .lead {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        /* Tarjetas de información modernizadas */
        .info-card {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            padding: 30px;
            margin-bottom: 30px;
            transition: var(--transition);
            border-top: 4px solid var(--color-primary);
        }

        .info-card:hover, .info-card:focus-within {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .info-card h4 {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 20px;
            font-family: var(--font-heading);
            position: relative;
            padding-left: 15px;
        }

        .info-card h4::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--color-primary);
            border-radius: 2px;
        }

        .info-card ul {
            padding-left: 20px;
        }

        .info-card ul li {
            margin-bottom: 10px;
            position: relative;
        }

        .info-card ul li strong {
            color: var(--color-secondary);
        }

        /* Galería modernizada */
        .tradition-gallery {
            margin: 50px 0;
        }

        .gallery-item {
            margin-bottom: 30px;
            border-radius: var(--radius-md);
            overflow: hidden;
            position: relative;
            cursor: pointer;
            box-shadow: var(--shadow-md);
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img, .gallery-item:focus img {
            transform: scale(1.05);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: white;
            padding: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-caption, .gallery-item:focus .gallery-caption {
            opacity: 1;
        }

        /* Citas modernizadas */
        .quote-block {
            background-color: rgba(76, 175, 80, 0.05);
            border-left: 5px solid var(--color-primary);
            padding: 25px;
            margin: 40px 0;
            position: relative;
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
        }

        .quote-block blockquote {
            font-style: italic;
            margin: 0;
            padding: 0 0 0 30px;
            font-size: 1.1rem;
            color: #555;
        }

        .quote-block blockquote::before {
            content: '"';
            font-size: 70px;
            color: rgba(76, 175, 80, 0.2);
            position: absolute;
            top: -20px;
            left: 15px;
            font-family: Georgia, serif;
        }

        .quote-block figcaption {
            margin-top: 15px;
            text-align: right;
            font-weight: 500;
            color: var(--color-secondary);
        }

        /* Tabs modernizados */
        .tradition-tabs {
            margin: 50px 0;
        }

        .nav-tabs {
            border-bottom: none;
            gap: 10px;
        }

        .nav-tabs .nav-link {
            color: var(--color-dark);
            border: none;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
            background-color: rgba(76, 175, 80, 0.05);
            transition: var(--transition);
        }

        .nav-tabs .nav-link:hover {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--color-primary);
        }

        .nav-tabs .nav-link.active {
            color: white;
            background-color: var(--color-primary);
            box-shadow: var(--shadow-sm);
        }

        .tab-content {
            padding: 35px;
            background: white;
            border-radius: 0 var(--radius-md) var(--radius-md) var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        /* Línea de tiempo modernizada */
        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 50px auto;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background: linear-gradient(to bottom, var(--color-primary), var(--color-secondary));
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -2px;
            border-radius: 2px;
        }

        .timeline-item {
            padding: 15px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-content {
            padding: 25px;
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .timeline-content h4 {
            color: var(--color-primary);
            margin-bottom: 10px;
            font-weight: 600;
            font-family: var(--font-heading);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: white;
            border: 4px solid var(--color-primary);
            border-radius: 50%;
            top: 20px;
            z-index: 1;
            box-shadow: var(--shadow-sm);
        }

        .timeline-item:nth-child(odd)::after {
            right: -10px;
        }

        .timeline-item:nth-child(even)::after {
            left: -10px;
        }

        /* Iconos de características */
        .feature-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 30px;
        }

        .feature-icon i {
            font-size: 2.5rem;
            color: var(--color-primary);
            margin-bottom: 15px;
            background-color: rgba(76, 175, 80, 0.1);
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .feature-icon:hover i {
            transform: scale(1.1);
            background-color: var(--color-primary);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .feature-icon h5 {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--color-dark);
            font-family: var(--font-heading);
        }

        /* Botón para volver arriba modernizado */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: var(--color-primary);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 1000;
            border: none;
            box-shadow: var(--shadow-md);
        }

        .back-to-top:hover, .back-to-top:focus {
            background-color: var(--color-secondary);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        /* Footer modernizado */
        .custom-footer {
            background-color: var(--color-dark);
            color: white;
            padding: 70px 0 30px;
            margin-top: 70px;
            position: relative;
        }

        .custom-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--color-primary), var(--color-secondary), var(--color-accent));
        }

        .custom-footer h3 {
            color: var(--color-primary);
            margin-bottom: 25px;
            font-weight: 600;
            font-family: var(--font-heading);
            position: relative;
            padding-bottom: 10px;
        }

        .custom-footer h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: var(--color-primary);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .footer-links a::before {
            content: '\f105';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 8px;
            color: var(--color-primary);
            transition: var(--transition);
        }

        .footer-links a:hover, .footer-links a:focus {
            color: var(--color-primary);
            transform: translateX(5px);
        }

        .footer-links a:hover::before {
            transform: translateX(3px);
        }

        .social-icons {
            margin-top: 20px;
        }

        .social-icons a {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            margin-right: 15px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .social-icons a:hover, .social-icons a:focus {
            color: white;
            background-color: var(--color-primary);
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .copyright {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Mejoras responsive */
        @media (max-width: 992px) {
            .tradition-banner h1 {
                font-size: 2.5rem;
            }
            
            .tradition-section h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .tradition-banner {
                padding: 80px 0;
            }
            
            .tradition-banner h1 {
                font-size: 2rem;
            }
            
            .tradition-banner p {
                font-size: 1.1rem;
            }
            
            .tradition-section {
                padding: 50px 0;
            }
            
            .tradition-section h2 {
                font-size: 1.8rem;
            }
            
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-item:nth-child(even) {
                left: 0;
            }
            
            .timeline-item::after {
                left: 21px;
            }
            
            .timeline-item:nth-child(odd)::after {
                right: auto;
            }
            
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .tab-content {
                padding: 25px 20px;
            }
            
            .info-card {
                padding: 20px;
            }
            
            .auth-section {
                margin-top: 15px;
                justify-content: center;
                width: 100%;
            }
            
            .user-welcome, .auth-buttons {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .tradition-banner h1 {
                font-size: 1.8rem;
            }
            
            .tradition-banner p {
                font-size: 1rem;
            }
            
            .tradition-section h2 {
                font-size: 1.6rem;
            }
            
            .tradition-section h3 {
                font-size: 1.4rem;
            }
            
            .info-card {
                padding: 15px;
            }
            
            .quote-block {
                padding: 20px;
            }
            
            .quote-block blockquote {
                padding-left: 15px;
                font-size: 1rem;
            }
            
            .quote-block blockquote::before {
                font-size: 50px;
                top: -15px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado de la página -->
    <header class="fixed-top">
        <nav class="navbar navbar-expand-lg" aria-label="Navegación principal">
            <div class="container-fluid">
                <div class="header-logo">
                    <a href="index.php" aria-label="Ir a la página de inicio">
                        <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120"> 
                    </a>
                </div>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                        aria-controls="navbarMain" aria-expanded="false" aria-label="Mostrar menú de navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse nav-links" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="tradiciones.php" aria-current="page">Tradiciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="productos.php">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Historias_comunidad.php">Historias</a>
                        </li>
                        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link admin-link" href="admin_home.php">
                                <i class="fas fa-tachometer-alt"></i> Panel Admin
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="auth-section ms-auto">
                        <?php if(isset($_SESSION['usuario'])): ?>
                            <div class="user-welcome">
                                <span class="user-name">
                                    <i class="fas fa-user-circle"></i> 
                                    <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                                </span>
                                <a href="logout.php" class="logout-btn" aria-label="Cerrar sesión">
                                    <i class="fas fa-sign-out-alt"></i> Salir
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="auth-buttons">
                                <a href="login.php" class="login-btn" aria-label="Iniciar sesión">
                                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                </a>
                                <a href="register.php" class="register-btn" aria-label="Registrarse">
                                    <i class="fas fa-user-plus"></i> Registrarse
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="contenido-principal">
        <!-- Banner principal con título y descripción -->
        <section class="tradition-banner" aria-labelledby="banner-title">
            <div class="container">
                <h1 id="banner-title" data-aos="fade-up">San Basilio de Palenque</h1>
                <p data-aos="fade-up" data-aos-delay="200">Música, Rituales y Medicina Tradicional en el Primer Pueblo Libre de América</p>
            </div>
        </section>

        <!-- Introducción -->
        <section class="tradition-section" aria-labelledby="intro-title">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto" data-aos="fade-up">
                        <h2 id="intro-title" class="visually-hidden">Introducción</h2>
                        <p class="lead">San Basilio de Palenque, corregimiento de Mahates en el departamento de Bolívar, Colombia, se erige como un faro de resistencia cultural y autonomía. Declarado Patrimonio Inmaterial de la Humanidad por la UNESCO en 2005, este pueblo fundado por cimarrones africanos en el siglo XVII preserva prácticas ancestrales que fusionan raíces bantúes con adaptaciones locales.</p>
                        
                        <p>Su música, ritos funerarios y sistemas médicos tradicionales no solo reflejan una herencia africana vibrante, sino también procesos de adaptación y resistencia que han definido su identidad. Este informe explora tres pilares fundamentales de su cultura: las expresiones musicales desde los tambores tradicionales hasta fusiones contemporáneas, el ritual del Lumbalú como eje de su cosmovisión, y el uso de plantas medicinales basado en una clasificación térmica única.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contexto Histórico y Cultural -->
        <section class="tradition-section" id="contexto" aria-labelledby="contexto-title">
            <div class="container">
                <h2 id="contexto-title" data-aos="fade-right">Contexto Histórico y Cultural</h2>
                
                <div class="row">
                    <div class="col-md-6" data-aos="fade-up">
                        <div class="info-card">
                            <h3>Orígenes y Significado como Primer Territorio Libre</h3>
                            <p>Fundado en el siglo XVII por esclavizados fugitivos liderados por Benkos Biohó, San Basilio de Palenque se consolidó como el primer pueblo libre de América tras un acuerdo con la Corona española en 1691. Su aislamiento geográfico en los Montes de María permitió la preservación de tradiciones africanas, particularmente del grupo étnico bantú del Congo-Angola, que se mezclaron con elementos indígenas y coloniales.</p>
                            <p>Este enclave se organizó bajo el sistema ma-kuagro, redes de apoyo comunitario basadas en grupos etarios que aún hoy regulan la solidaridad y el trabajo colectivo.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <h3>Lengua Palenquera: Un Código de Resistencia</h3>
                            <p>El palenquero, única lengua criolla de base léxica española y gramática bantú en América, funciona como marcador identitario. Su uso en cantos rituales y narrativas orales ha sido fundamental para transmitir conocimientos ancestrales y mantener la cohesión social frente a la discriminación.</p>
                            <p>Esta lengua no solo codifica su visión del mundo, sino que también estructura prácticas médicas y rituales, evidenciando cómo el lenguaje articula su resistencia cultural.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Línea de tiempo histórica -->
                <h3 data-aos="fade-right" class="mt-5">Cronología Histórica</h3>
                <div class="timeline" data-aos="fade-up">
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>1599</h4>
                            <p>Benkos Biohó lidera una rebelión de esclavizados y escapa hacia los Montes de María.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>1603-1621</h4>
                            <p>Período de establecimiento y consolidación del palenque como territorio autónomo.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>1691</h4>
                            <p>Acuerdo con la Corona española que reconoce la libertad del palenque.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>Siglos XVIII-XIX</h4>
                            <p>Desarrollo de prácticas culturales distintivas y consolidación de la lengua palenquera.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>2005</h4>
                            <p>La UNESCO declara el espacio cultural de San Basilio de Palenque como Patrimonio Inmaterial de la Humanidad.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Expresiones Musicales -->
        <section class="tradition-section" id="musica" aria-labelledby="musica-title">
            <div class="container">
                <h2 id="musica-title" data-aos="fade-right">Expresiones Musicales</h2>
                <p data-aos="fade-up" class="lead">Del Tambor Ancestral a las Fusiones Contemporáneas</p>
                
                <!-- Tabs para organizar la información musical -->
                <div class="tradition-tabs" data-aos="fade-up">
                    <ul class="nav nav-tabs" id="musicTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="generos-tab" data-bs-toggle="tab" data-bs-target="#generos" 
                                    type="button" role="tab" aria-controls="generos" aria-selected="true">
                                <i class="fas fa-music me-2"></i>Géneros Tradicionales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="instrumentos-tab" data-bs-toggle="tab" data-bs-target="#instrumentos" 
                                    type="button" role="tab" aria-controls="instrumentos" aria-selected="false">
                                <i class="fas fa-drum me-2"></i>Instrumentación
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="innovaciones-tab" data-bs-toggle="tab" data-bs-target="#innovaciones" 
                                    type="button" role="tab" aria-controls="innovaciones" aria-selected="false">
                                <i class="fas fa-compact-disc me-2"></i>Innovaciones
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="musicTabsContent">
                        <div class="tab-pane fade show active" id="generos" role="tabpanel" aria-labelledby="generos-tab">
                            <h4>Géneros Tradicionales y su Simbología</h4>
                            <p>La música palenquera se estructura alrededor del tambor, instrumento que simboliza la libertad conquistada. Géneros como el bullerengue sentao, el son de negros y el lumbalú emplean polirritmias complejas que evocan patrones africanos.</p>
                            <p>El son palenquero, surgido en los años 30 al mezclar son cubano con tradiciones locales, se caracteriza por letras en palenquero y el uso del llamador, tambor que marca el ritmo base. Estas expresiones se vinculan a ciclos vitales: el bullerengue acompaña nacimientos y bodas, mientras el lumbalú guía los ritos mortuorios.</p>
                            
                            <figure class="quote-block">
                                <blockquote>
                                    "El tambor no es solo un instrumento, es la voz de nuestros ancestros que sigue hablando a través de nuestras manos."
                                </blockquote>
                                <figcaption>— Rafael Cassiani, músico palenquero</figcaption>
                            </figure>
                        </div>
                        <div class="tab-pane fade" id="instrumentos" role="tabpanel" aria-labelledby="instrumentos-tab">
                            <h4>Instrumentación y Estructura Comunitaria</h4>
                            <p>La fabricación de tambores como el pechiche (tambor hembra) y el cununo (tambor macho) sigue técnicas ancestrales usando troncos de árboles como el caracolí.</p>
                            <p>Los músicos, organizados en agrupaciones como el Sexteto Tabalá o Batata y su Rumba Palenquera, heredan roles específicos: los cantadoras (mujeres vocalistas) lideran las letras, mientras los tamboreros mantienen diálogos rítmicos que imitan lenguajes tonales africanos.</p>
                            
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-drum"></i>
                                        <h5>Tambor Pechiche</h5>
                                        <p>Tambor principal, de tono grave, que marca el ritmo base.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-drum"></i>
                                        <h5>Tambor Llamador</h5>
                                        <p>Tambor pequeño que "llama" o marca el tiempo.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-microphone"></i>
                                        <h5>Cantadoras</h5>
                                        <p>Mujeres que preservan los cantos tradicionales.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="innovaciones" role="tabpanel" aria-labelledby="innovaciones-tab">
                            <h4>Innovaciones y Diálogos con Géneros Globales</h4>
                            <p>Las nuevas generaciones han creado fusiones como el electropalenquero de Kombilesa Mí, que integra sintetizadores con tambores tradicionales. Franklyn Tejedor "Lamparita", pionero del colectivo Mitú, combina samples de cantos ancestrales con beats electrónicos, demostrando cómo la tradición se reinterpreta sin perder su esencia.</p>
                            <p>Esta evolución refleja una estrategia de preservación activa, donde el hip hop y la electrónica se convierten en vehículos para narrar luchas contemporáneas contra el racismo y la marginación.</p>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <h5>Kombilesa Mí</h5>
                                        <p>Colectivo que fusiona rap en lengua palenquera con ritmos tradicionales, creando un puente entre generaciones.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <h5>Mitú</h5>
                                        <p>Proyecto de música electrónica que incorpora samples de cantos ancestrales palenqueros en producciones contemporáneas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Galería de imágenes musicales -->
                <div class="tradition-gallery" data-aos="fade-up">
                    <h3 class="visually-hidden">Galería de imágenes musicales</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1511192336575-5a79af67a629?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                     alt="Tambores tradicionales palenqueros" loading="lazy" width="400" height="250">
                                <figcaption class="gallery-caption">Tambores tradicionales palenqueros</figcaption>
                            </figure>
                        </div>
                        <div class="col-md-4">
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                     alt="Cantadoras preservando tradiciones orales" loading="lazy" width="400" height="250">
                                <figcaption class="gallery-caption">Cantadoras preservando tradiciones orales</figcaption>
                            </figure>
                        </div>
                        <div class="col-md-4">
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                     alt="Fusión de tradición y modernidad" loading="lazy" width="400" height="250">
                                <figcaption class="gallery-caption">Fusión de tradición y modernidad</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- El Lumbalú -->
        <section class="tradition-section" id="lumbalu" aria-labelledby="lumbalu-title">
            <div class="container">
                <h2 id="lumbalu-title" data-aos="fade-right">El Lumbalú: Ritual Fúnebre y Memoria Colectiva</h2>
                
                <div class="row">
                    <div class="col-lg-6" data-aos="fade-up">
                        <h3>Estructura y Simbolismo del Rito</h3>
                        <p>El Lumbalú, practicado durante nueve noches tras un fallecimiento, sintetiza creencias africanas y católicas. Su nombre deriva del kikongo lumbalu (llanto colectivo), y estructura un proceso de duelo que garantiza el tránsito del alma al mundo ancestral.</p>
                        <p>Los kuagros organizan veladas con cantos responsoriales (chandé) y juegos tradicionales como la puya, donde los participantes deben mantener vigilia mediante competencias físicas y mentales.</p>
                    </div>
                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <h4>Componentes Rituales</h4>
                            <ul>
                                <li><strong>Cantos de Lumbalú:</strong> Dirigidos por una cantadora, relatan la vida del difunto y guían su espíritu. Las letras en palenquero mezclan invocaciones a santos católicos y deidades africanas como Sángó.</li>
                                <li><strong>Ofrendas Alimenticias:</strong> El sancocho de gallina se prepara con hierbas como el yalua para purificar a los dolientes.</li>
                                <li><strong>Danças Circulares:</strong> Los participantes forman círculos concéntricos que simbolizan la continuidad entre vida y muerte, moviéndose en dirección contraria a las manecillas del reloj para "deshacer" el tiempo terrenal.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-lg-12" data-aos="fade-up">
                        <h3>Significado Antropológico</h3>
                        <p>El Lumbalú opera como mecanismo de cohesión social: al compartir el dolor, la comunidad reafirma su identidad. Comparado con el candomblé brasileño y la santería cubana, este ritual destaca por su énfasis en la participación colectiva sobre el trance individual, reflejando valores comunitarios bantúes.</p>
                        <p>Además, funciona como archivo oral: los cantos preservan historias familiares y episodios de resistencia cimarrona que no aparecen en registros escritos.</p>
                        
                        <figure class="quote-block">
                            <blockquote>
                                "En el Lumbalú no solo despedimos a un ser querido, sino que reafirmamos quiénes somos como pueblo. Cada canto es un hilo que nos conecta con nuestros ancestros."
                            </blockquote>
                            <figcaption>— Graciela Salgado, cantadora de Palenque</figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        <!-- Medicina Tradicional -->
        <section class="tradition-section" id="medicina" aria-labelledby="medicina-title">
            <div class="container">
                <h2 id="medicina-title" data-aos="fade-right">Medicina Tradicional: Saberes Herbarios y Clasificaciones Térmicas</h2>
                
                <div class="row">
                    <div class="col-lg-12" data-aos="fade-up">
                        <h3>Sistema de Salud Ancestral</h3>
                        <p>La medicina palenquera se basa en el equilibrio entre "frío" y "caliente", categorías que regulan tanto las enfermedades como los ciclos naturales. Esta clasificación, de origen africano, asocia dolencias "calientes" (fiebres, inflamaciones) con plantas "frías" como la albahaca negra (Ocimum basilicum), y viceversa.</p>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="info-card">
                            <h4>Plantas "Frías"</h4>
                            <ul>
                                <li><strong>Albahaca Negra:</strong> Para bajar fiebres e inflamaciones.</li>
                                <li><strong>Hierba Limón:</strong> Calma dolores de cabeza y reduce la presión arterial.</li>
                                <li><strong>Toronjil:</strong> Alivia problemas digestivos y calma nervios.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="info-card">
                            <h4>Plantas "Calientes"</h4>
                            <ul>
                                <li><strong>Jengibre:</strong> Estimula la circulación y combate resfriados.</li>
                                <li><strong>Ruda:</strong> Alivia dolores menstruales y problemas respiratorios.</li>
                                <li><strong>Canela:</strong> Mejora la digestión y fortalece el sistema inmunológico.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <h4>Plantas de Protección</h4>
                            <ul>
                                <li><strong>Cabalonga:</strong> Semillas usadas en rituales de protección contra "brujerías".</li>
                                <li><strong>Indio Desnudo:</strong> Corteza antiinflamatoria para mordeduras de serpiente.</li>
                                <li><strong>Guanábana:</strong> Hojas en decocción para infecciones respiratorias.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-lg-8" data-aos="fade-up">
                        <h3>Transmisión del Conocimiento y Retos Actuales</h3>
                        <p>Los nganga (médicos tradicionales) aprenden mediante apprentissage oral, memorizando propiedades de plantas en canciones y refranes. Sin embargo, la migración juvenil y la penetración de la medicina occidental amenazan esta tradición.</p>
                        <p>Iniciativas como el Jardín Botánico Ancestral, donde cultivan especies clave, buscan preservar estos saberes mediante talleres intergeneracionales.</p>
                    </div>
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <figure class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1471193945509-9ad0617afabf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                 alt="Jardín de plantas medicinales tradicionales" loading="lazy" width="400" height="250">
                            <figcaption class="gallery-caption">Jardín de plantas medicinales tradicionales</figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        <!-- Conclusiones -->
        <section class="tradition-section" id="conclusiones" aria-labelledby="conclusiones-title">
            <div class="container">
                <h2 id="conclusiones-title" data-aos="fade-right">Conclusiones: Palenque como Modelo de Resiliencia Cultural</h2>
                
                <div class="row">
                    <div class="col-lg-8 mx-auto" data-aos="fade-up">
                        <p>San Basilio de Palenque ejemplifica cómo comunidades afrodiaspóricas han transformado la opresión en creatividad cultural. Su música, rituales y medicina no son reliquias estáticas, sino sistemas dinámicos que dialogan con la modernidad mientras preservan núcleos identitarios.</p>
                        
                        <div class="info-card mt-4">
                            <h4>Aspectos Clave a Destacar</h4>
                            <ul>
                                <li><strong>Interconexión de Elementos:</strong> La lengua, música y medicina forman un tejido indivisible.</li>
                                <li><strong>Agentes de Cambio:</strong> Colectivos juveniles como Kombilesa Mí muestran que la innovación fortalece, no diluye, la tradición.</li>
                                <li><strong>Amenazas y Oportunidades:</strong> El turismo masivo y la apropiación cultural exigen modelos de divulgación ética que prioricen la voz palenquera.</li>
                            </ul>
                        </div>
                        
                        <p class="mt-4">Al publicar sobre Palenque, se debe evitar el exotismo, enfatizando en cambio su papel como faro de resistencia y fuente de soluciones ancestrales a problemas contemporáneos, desde salud comunitaria hasta justicia social.</p>
                        
                        <figure class="quote-block">
                            <blockquote>
                                "Palenque no es un museo viviente, sino una comunidad que ha sabido adaptar sus tradiciones para sobrevivir y prosperar en un mundo cambiante."
                            </blockquote>
                            <figcaption>— Manuel Pérez, historiador</figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Botón para volver arriba -->
    <button type="button" class="back-to-top" id="backToTop" aria-label="Volver al inicio de la página">
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>

    <!-- Pie de página personalizado -->
    <footer class="custom-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h3>San Basilio de Palenque</h3>
                    <p>Primer pueblo libre de América y Patrimonio Inmaterial de la Humanidad por la UNESCO.</p>
                </div>
                <div class="col-md-4">
                    <h3>Enlaces Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="tradiciones.php">Tradiciones</a></li>
                        <li><a href="productos.php">Productos</a></li>
                        <li><a href="historias.php">Historias</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3>Contacto</h3>
                    <address>
                        <p><i class="fas fa-envelope me-2" aria-hidden="true"></i> <a href="mailto:info@sanbasiliodepalenque.com">info@sanbasiliodepalenque.com</a></p>
                        <p><i class="fas fa-phone me-2" aria-hidden="true"></i> <a href="tel:+573123456789">+57 312 345 6789</a></p>
                    </address>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="copyright">© <?php echo date('Y'); ?> San Basilio de Palenque. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar AOS con configuración optimizada
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 100,
                delay: 0
            });
            
            // Gestión del botón para volver arriba
            const backToTopButton = document.getElementById('backToTop');
            
            function toggleBackToTopButton() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('visible');
                } else {
                    backToTopButton.classList.remove('visible');
                }
            }
            
            window.addEventListener('scroll', toggleBackToTopButton);
            
            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Navegación suave para enlaces internos
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        const headerOffset = 100;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Gestión de tabs
            const firstTabEl = document.querySelector('#musicTabs button:first-child');
            if (firstTabEl) {
                const firstTab = new bootstrap.Tab(firstTabEl);
                firstTab.show();
            }
            
            const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(e) {
                    const targetId = e.target.getAttribute('data-bs-target').substring(1);
                    history.replaceState(null, null, `#${targetId}`);
                });
            });
            
            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                const tabToActivate = document.querySelector(`[data-bs-target="#${hash}"]`);
                if (tabToActivate) {
                    const tab = new bootstrap.Tab(tabToActivate);
                    tab.show();
                }
            }
            
            // Mejorar accesibilidad para usuarios de teclado
            const galleryItems = document.querySelectorAll('.gallery-item');
            galleryItems.forEach(item => {
                item.setAttribute('tabindex', '0');
                item.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        this.querySelector('img').click();
                    }
                });
            });
            
            // Lazy loading para imágenes
            if ('IntersectionObserver' in window) {
                const lazyImages = document.querySelectorAll('img[loading="lazy"]');
                
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                }});
                });
                
                lazyImages.forEach(img => {
                    imageObserver.observe(img);
                });
            }
            
            // Añadir efecto de hover a las tarjetas de información
            const infoCards = document.querySelectorAll('.info-card');
            infoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = 'var(--shadow-lg)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--shadow-md)';
                });
            });
            
            // Añadir efecto de desplazamiento para la navegación
            const navLinks = document.querySelectorAll('.navbar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Cerrar el menú móvil si está abierto
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                        bsCollapse.hide();
                    }
                });
            });
            
            // Añadir efecto de scroll para el header
            const header = document.querySelector('.fixed-top');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    header.style.padding = '0.3rem 1rem';
                    header.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.1)';
                } else {
                    header.style.padding = '0.5rem 1.5rem';
                    header.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.05)';
                }
            });
        });
    </script>
</body>
</html>