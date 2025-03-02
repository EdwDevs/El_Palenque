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
        
        /* Estilos generales del cuerpo de la página */
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
        
        /* Estilos del encabezado */
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition-normal);
        }
        
        /* Efecto de scroll para el header */
        header.scrolled {
            padding: 0.5rem 0;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* Logo del sitio */
        .navbar-brand {
            color: var(--color-primary);
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-normal);
        }
        
        .navbar-brand:hover {
            color: var(--color-secondary);
            transform: translateY(-2px);
        }
        
        .navbar-brand img {
            max-height: 40px;
            border-radius: 5px;
            border: 2px solid var(--color-primary);
            transition: var(--transition-normal);
        }
        
        .navbar-brand:hover img {
            border-color: var(--color-secondary);
            transform: scale(1.05);
        }
        
        /* Enlaces de navegación */
        .navbar-nav .nav-link {
            color: var(--color-secondary);
            font-weight: 600;
            margin-right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--color-primary);
            background: rgba(76, 175, 80, 0.1);
            transform: translateY(-2px);
        }
        
        /* Indicador de enlace activo */
        .navbar-nav .nav-link.active {
            color: var(--color-primary);
            background: rgba(255, 87, 34, 0.1);
            position: relative;
        }
        
        .navbar-nav .nav-link.active::after {
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
        
        /* Mensaje de bienvenida al usuario */
        .user-welcome {
            color: var(--color-primary);
            font-weight: 600;
            font-size: 1.1rem;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            margin-right: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-welcome:hover {
            transform: translateY(-2px);
            background-color: var(--color-hover);
        }
        
        /* Botón de salir */
        .btn-salir {
            background-color: var(--color-primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-salir:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Sección principal de bienvenida */
        .hero {
            text-align: center;
            padding: 3rem 2rem;
            background: rgba(255, 255, 255, 0.9);
            margin: 2rem auto;
            max-width: 800px;
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            animation: fadeIn 0.8s ease-in-out;
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
        
        /* Botón de información */
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
        }
        
        .btn-info:hover {
            background-color: var(--color-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Sección de servicios para usuarios */
        .services {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto 2rem;
        }
        
        .services-title {
            color: var(--color-light);
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 0.5rem;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .services-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--color-light);
            border-radius: 3px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .service-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition-normal);
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        .service-icon {
            font-size: 3rem;
            color: var(--color-accent);
            margin-bottom: 1rem;
        }
        
        .service-title {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .service-description {
            color: var(--color-text);
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }
        
        .service-link {
            background-color: var(--color-secondary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-normal);
            margin-top: auto;
        }
        
        .service-link:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
        }
        
        /* Pie de página */
        footer {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            color: var(--color-text);
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
        
        /* Estilos responsivos para dispositivos móviles */
        @media (max-width: 768px) {
            .hero {
                margin: 1rem;
                padding: 2rem 1rem;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .services {
                padding: 1rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .user-welcome {
                font-size: 0.9rem;
                padding: 0.4rem 0.8rem;
            }
            
            .btn-salir {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links, .social-icons {
                justify-content: center;
            }
        }
        
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
        <nav class="navbar navbar-expand-lg container">
            <a class="navbar-brand" href="index.php" title="Página de inicio">
                <img src="palenque.jpeg" alt="Logo Sabor Colombiano" width="40" height="40">
                Sabor Colombiano
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
                
                <span class="user-welcome">
                    <i class="fas fa-user"></i> ¡Hola, <?php echo $username; ?>!
                </span>
                
                <a href="logout.php" title="Cerrar sesión">
                    <button class="btn-salir ms-3">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </button>
                </a>
            </div>
        </nav>
    </header>

    <!-- SECCIÓN HERO: Contiene el mensaje de bienvenida -->
    <section class="hero">
        <h2>¡Bienvenido a tu Panel de Usuario!</h2>
        <p>Explora y descubre todos los servicios exclusivos que tenemos para ti. Disfruta de la esencia de nuestra tierra: alegría, color y tradición.</p>
        <a href="#servicios" class="btn-info">
            <i class="fas fa-arrow-down"></i> Ver Servicios
        </a>
    </section>

    <!-- SECCIÓN DE SERVICIOS: Muestra los servicios disponibles para el usuario -->
    <section class="services" id="servicios">
        <h3 class="services-title">Servicios Exclusivos</h3>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h4 class="service-title">Tradiciones</h4>
                <p class="service-description">Accede a nuestra colección exclusiva de recetas tradicionales colombianas, con instrucciones paso a paso y consejos de nuestros chefs.</p>
                <a href="#" class="service-link">
                    <i class="fas fa-arrow-right"></i> Conoce Mas
                </a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4 class="service-title">Productos</h4>
                <p class="service-description">Mantente informado sobre nuestros próximos eventos culturales, talleres de cocina y celebraciones tradicionales.</p>
                <a href="#" class="service-link">
                    <i class="fas fa-arrow-right"></i> Ver Productos
                </a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h4 class="service-title">Historias de la Comunidad</h4>
                <p class="service-description">Actualiza tus datos personales, cambia tu contraseña y personaliza tus preferencias en nuestra plataforma.</p>
                <a href="#perfil" class="service-link">
                    <i class="fas fa-arrow-right"></i> Cuentanos tu Historia
                </a>
            </div>
        </div>
    </section>
    
    <!-- SECCIÓN DE PERFIL: Permite al usuario gestionar su información -->
    <section class="hero" id="perfil">
        <h2>Mi Perfil</h2>
        <p>Gestiona tu información personal y preferencias de usuario.</p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="editar_perfil.php" class="btn-info">
                <i class="fas fa-user-edit"></i> Editar Datos
            </a>
            <a href="cambiar_contrasena.php" class="btn-info" style="background-color: var(--color-secondary); color: white;">
                <i class="fas fa-key"></i> Cambiar Contraseña
            </a>
        </div>
    </section>
    
    <!-- SECCIÓN DE CONTACTO: Información de contacto -->
    <section class="hero" id="contacto" style="margin-bottom: 5rem;">
        <h2>Contáctanos</h2>
        <p>¿Tienes alguna pregunta o sugerencia? Estamos aquí para ayudarte.</p>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form id="contactForm">
                            <div class="mb-3">
                                <label for="asunto" class="form-label">Asunto</label>
                                <input type="text" class="form-control" id="asunto" required>
                            </div>
                            <div class="mb-3">
                                <label for="mensaje" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="mensaje" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn-info" style="border: none;">
                                <i class="fas fa-paper-plane"></i> Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-center gap-4">
            <div class="text-center">
                <i class="fas fa-phone fa-2x mb-2" style="color: var(--color-secondary);"></i>
                <p>+57 123 456 7890</p>
            </div>
            <div class="text-center">
                <i class="fas fa-envelope fa-2x mb-2" style="color: var(--color-secondary);"></i>
                <p>info@saborcolombiano.com</p>
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