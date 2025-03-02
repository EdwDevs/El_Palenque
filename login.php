<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Sabor Colombiano</title>
    <!-- Bootstrap CSS: Incluye los estilos de Bootstrap para un diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Montserrat: Añade tipografía profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome: Para iconos como el ojo para mostrar/ocultar contraseña -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados: Define estilos específicos para la página -->
    <style>
        /* Estilos generales del cuerpo de la página */
        body {
            /* Fondo con degradado de colores que representan la bandera colombiana */
            background: linear-gradient(135deg, #FFC107, #FF5722, #4CAF50);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            color: #333;
            padding: 20px; /* Añadido para mejorar la visualización en dispositivos móviles */
        }
        
        /* Contenedor principal del formulario de login */
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            max-width: 450px;
            margin: 0 auto;
            border: 1px solid #FFC107;
            /* Añadido para mejorar la animación al cargar */
            animation: fadeIn 0.5s ease-in-out;
        }
        
        /* Animación de aparición suave */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Estilos del encabezado con el logo */
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        /* Estilos de la imagen del logo */
        .header img {
            max-width: 120px;
            border-radius: 10px;
            border: 3px solid #FF5722;
            transition: transform 0.3s ease;
        }
        
        /* Efecto hover para la imagen del logo */
        .header img:hover {
            transform: scale(1.05);
        }
        
        /* Estilos del título principal */
        h2 {
            color: #FF5722;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        /* Estilos de las etiquetas de los campos del formulario */
        .form-label {
            color: #4CAF50;
            font-weight: 600;
            font-size: 1rem;
        }
        
        /* Estilos de los campos de entrada del formulario */
        .form-control {
            border: 2px solid #FFC107;
            border-radius: 10px;
            padding: 0.75rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        /* Estilos cuando el campo está enfocado */
        .form-control:focus {
            border-color: #FF5722;
            box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
            outline: none;
        }
        
        /* Contenedor para el campo de contraseña con el icono de mostrar/ocultar */
        .password-container {
            position: relative;
        }
        
        /* Estilos para el icono de mostrar/ocultar contraseña */
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #4CAF50;
            transition: color 0.3s;
        }
        
        /* Efecto hover para el icono de mostrar/ocultar contraseña */
        .password-toggle:hover {
            color: #FF5722;
        }
        
        /* Estilos del botón de inicio de sesión */
        .btn-login {
            background-color: #4CAF50;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        /* Efecto hover para el botón de inicio de sesión */
        .btn-login:hover {
            background-color: #FF5722;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Estilos para la sección de enlaces adicionales */
        .extra-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        /* Estilos para los enlaces de registro y recuperación de contraseña */
        .extra-links a {
            color: #FF5722;
            text-decoration: none;
            font-weight: 600;
            display: block; /* Hace que cada enlace ocupe su propia línea */
            margin-bottom: 0.5rem; /* Espaciado entre enlaces */
            transition: color 0.3s, transform 0.2s;
        }
        
        /* Efecto hover para los enlaces */
        .extra-links a:hover {
            color: #4CAF50;
            text-decoration: underline;
            transform: translateX(5px);
        }
        
        /* Estilos del pie de página */
        footer {
            text-align: center;
            padding: 1rem;
            color: #333;
            font-size: 0.9rem;
            margin-top: 2rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
        }
        
        /* Ajustes responsivos para dispositivos móviles */
        @media (max-width: 576px) {
            .login-container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            footer {
                position: relative;
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Sección del encabezado con logo -->
        <div class="header">
            <a href="home.php">
                <img src="palenque.jpeg" alt="Palenquera Colombiana" title="Volver a la página principal">
            </a>
        </div>
        
        <!-- Título del formulario de inicio de sesión -->
        <h2>Inicio de Sesión</h2>
        
        <!-- Formulario para iniciar sesión -->
        <form action="validar_inicio.php" method="post">
            <!-- Campo para el correo electrónico -->
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese su correo" required>
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
            </div>
            
            <!-- Campo para la contraseña con opción de mostrar/ocultar -->
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="contraseña" name="contraseña" placeholder="Ingrese su contraseña" required>
                    <span class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            
            <!-- Botón de inicio de sesión -->
            <div class="d-grid">
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                </button>
            </div>
            
            <!-- Sección de enlaces adicionales, incluyendo recuperar contraseña -->
            <div class="extra-links">
                <a href="register.php"><i class="fas fa-user-plus me-1"></i> ¿No tienes cuenta? Regístrate</a>
                <a href="recuperar_contraseña.php"><i class="fas fa-key me-1"></i> ¿Olvidaste tu contraseña? Recupérala</a>
            </div>
        </form>
    </div>

    <!-- Pie de página -->
    <footer>
        <p>© 2025 Sabor Colombiano - Diseñado con pasión.</p>
    </footer>

    <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para la funcionalidad de mostrar/ocultar contraseña -->
    <script>
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener referencias a los elementos del DOM
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('contraseña');
            
            // Añadir evento de clic al icono de mostrar/ocultar contraseña
            togglePassword.addEventListener('click', function() {
                // Cambiar el tipo de campo entre 'password' y 'text'
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Cambiar el icono entre ojo y ojo tachado
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>