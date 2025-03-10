<?php
// Iniciar sesión para gestionar datos del usuario
session_start();

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['usuario'])) {
    // Si ya hay sesión activa, redirigir según el rol
    $redirect = ($_SESSION['rol'] === 'admin') ? 'admin_home.php' : 'user_home.php';
    header("Location: $redirect");
    exit();
}

// Incluir la conexión a la base de datos para posibles validaciones
include('db.php');

// Variables para mensajes de error y valores del formulario
$error_msg = '';
$nombre_value = '';
$correo_value = '';
$rol_value = 'usuario'; // Valor predeterminado para el rol

// Manejo del formulario de registro mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización de los datos recibidos para prevenir ataques XSS y otros riesgos
    $nombre = trim(htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8'));
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $contraseña = $_POST['contraseña']; // Se procesará con hash más adelante
    $rol = htmlspecialchars($_POST['rol'], ENT_QUOTES, 'UTF-8');
    
    // Guardar valores para repoblar el formulario en caso de error
    $nombre_value = $nombre;
    $correo_value = $correo;
    $rol_value = $rol;

    // Validación de campos obligatorios
    if (empty($nombre) || empty($correo) || empty($contraseña) || empty($rol)) {
        $error_msg = 'Todos los campos son obligatorios';
    } 
    // Validación de formato de correo electrónico
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Por favor, ingrese un correo electrónico válido';
    }
    // Validación de longitud de contraseña
    elseif (strlen($contraseña) < 8) {
        $error_msg = 'La contraseña debe tener al menos 8 caracteres';
    }
    // Verificar si el correo ya existe en la base de datos
    else {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_msg = 'Este correo electrónico ya está registrado';
        } else {
            // Hashear la contraseña para mayor seguridad
            $hashed_password = password_hash($contraseña, PASSWORD_DEFAULT);
            
            // Insertar directamente en la base de datos en lugar de redirigir
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol, habilitado) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $nombre, $correo, $hashed_password, $rol);
            
            if ($stmt->execute()) {
                // Redirigir a la página de login con mensaje de éxito
                header("Location: login.php?registro=exitoso");
                exit();
            } else {
                $error_msg = 'Error al registrar el usuario: ' . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registro de nuevos usuarios en Sabor Colombiano - Únete a nuestra comunidad">
    <title>Registro - Sabor Colombiano</title>

    <!-- Bootstrap CSS: Incluye los estilos de Bootstrap para un diseño responsive y profesional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Montserrat: Añade tipografía elegante y profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome: Para incluir iconos en la interfaz -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos personalizados: Define estilos específicos para el formulario de registro -->
    <style>
    /* Variables CSS para mantener consistencia en colores y valores */
    :root {
        --color-primary: #FF5722;
        --color-secondary: #4CAF50;
        --color-accent: #FFC107;
        --color-text: #333333;
        --color-light: #FFFFFF;
        --color-hover: #FFF3E0;
        --color-error: #f44336;
        --border-radius: 10px;
        --box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        --transition-normal: all 0.3s ease;
    }

    /* ===== ESTILOS GLOBALES ===== */
    /* Configuración general del cuerpo de la página */
    body {
        background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        font-family: 'Montserrat', sans-serif;
        color: var(--color-text);
        position: relative;
        padding-bottom: 60px;
        /* Espacio para el footer */
    }

    /* ===== HEADER ===== */
    /* Configuración del encabezado fijo en la parte superior */
    header {
        background: rgba(255, 255, 255, 0.95);
        padding: 1rem 0;
        box-shadow: var(--box-shadow);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Contenedor del logo */
    .header-logo {
        text-align: center;
        flex-grow: 1;
    }

    /* Estilo del logo */
    .header-logo img {
        max-width: 120px;
        border-radius: var(--border-radius);
        border: 3px solid var(--color-primary);
        transition: transform 0.3s ease;
    }

    .header-logo img:hover {
        transform: scale(1.05);
    }

    /* Botón para volver al inicio */
    .btn-volver {
        background-color: var(--color-accent);
        color: var(--color-text);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        margin-left: 2rem;
        transition: var(--transition-normal);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-volver:hover {
        background-color: var(--color-primary);
        color: var(--color-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Espaciador para equilibrar el header */
    .header-spacer {
        width: 150px;
    }

    /* ===== CONTENEDOR DE REGISTRO ===== */
    /* Estilo del contenedor principal del formulario */
    .register-container {
        background: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: var(--box-shadow);
        max-width: 500px;
        width: 90%;
        margin: 8rem auto 2rem;
        border: 1px solid var(--color-accent);
        animation: fadeIn 0.8s ease-in-out;
    }

    /* Animación de aparición suave */
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

    /* Título del formulario */
    h3 {
        color: var(--color-primary);
        font-weight: 700;
        font-size: 2rem;
        text-align: center;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    /* Línea decorativa debajo del título */
    h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
        border-radius: 3px;
    }

    /* Mensaje de error */
    .error-message {
        color: var(--color-error);
        background-color: rgba(244, 67, 54, 0.1);
        border: 1px solid var(--color-error);
        border-radius: var(--border-radius);
        padding: 0.75rem;
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    /* ===== ELEMENTOS DEL FORMULARIO ===== */
    /* Etiquetas de los campos */
    .form-label {
        color: var(--color-secondary);
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Campos de entrada */
    .form-control {
        border: 2px solid var(--color-accent);
        border-radius: var(--border-radius);
        padding: 0.75rem;
        transition: var(--transition-normal);
    }

    .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
        outline: none;
    }

    /* Texto de ayuda para los campos */
    .form-text {
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    /* Contenedor para el campo de contraseña con icono */
    .password-container {
        position: relative;
    }

    /* Icono de ojo para mostrar/ocultar contraseña */
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--color-secondary);
        transition: var(--transition-normal);
        padding: 0.5rem;
        z-index: 10;
    }

    .password-toggle:hover {
        color: var(--color-primary);
    }

    /* Indicador de fortaleza de contraseña */
    .password-strength {
        height: 5px;
        margin-top: 0.5rem;
        border-radius: 3px;
        transition: var(--transition-normal);
    }

    /* Botón de registro */
    .btn-register {
        background-color: var(--color-secondary);
        border: none;
        padding: 0.75rem;
        font-weight: 600;
        color: white;
        border-radius: var(--border-radius);
        width: 100%;
        transition: var(--transition-normal);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-register:hover {
        background-color: var(--color-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Enlaces adicionales */
    .extra-links {
        text-align: center;
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .extra-links a {
        color: var(--color-primary);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition-normal);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .extra-links a:hover {
        color: var(--color-secondary);
        transform: translateY(-2px);
    }

    /* ===== FOOTER ===== */
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

    /* ===== RESPONSIVE ===== */
    /* Ajustes para dispositivos móviles */
    @media (max-width: 768px) {
        .register-container {
            padding: 1.5rem;
            margin-top: 7rem;
        }

        .btn-volver {
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .header-logo img {
            max-width: 80px;
        }

        h3 {
            font-size: 1.5rem;
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
    a:focus,
    button:focus,
    input:focus,
    select:focus {
        outline: 3px solid var(--color-accent);
        outline-offset: 2px;
    }
    </style>
</head>

<body>
    <!-- Header con logo y botón para volver -->
    <header>
        <a href="index.php" class="btn-volver" title="Volver a la página principal">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div class="header-logo">
            <a href="index.php" title="Ir a la página principal">
                <img src="palenque.jpeg" alt="Palenquera Colombiana" width="120" height="120">
            </a>
        </div>
        <div class="header-spacer"></div> <!-- Espaciador para equilibrar el header -->
    </header>

    <!-- Contenedor principal del formulario de registro -->
    <div class="register-container">
        <!-- Título del formulario de registro -->
        <h3>Registro de Usuario</h3>

        <!-- Mostrar mensaje de error si existe -->
        <?php if (!empty($error_msg)): ?>
        <div class="error-message" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Formulario para registrar un nuevo usuario -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="registerForm">
            <!-- Campo para el nombre del usuario -->
            <div class="mb-3">
                <label for="nombre" class="form-label">
                    <i class="fas fa-user"></i> Nombre
                </label>
                <input type="text" class="form-control" id="nombre" name="nombre"
                    placeholder="Digite su nombre completo" value="<?php echo $nombre_value; ?>" required minlength="3"
                    maxlength="50" aria-describedby="nombreHelp">
                <div id="nombreHelp" class="form-text">
                    <i class="fas fa-info-circle"></i> Ingrese su nombre completo.
                </div>
            </div>

            <!-- Campo para el correo electrónico -->
            <div class="mb-3">
                <label for="correo" class="form-label">
                    <i class="fas fa-envelope"></i> Correo Electrónico
                </label>
                <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@correo.com"
                    value="<?php echo $correo_value; ?>" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                    aria-describedby="correoHelp">
                <div id="correoHelp" class="form-text">
                    <i class="fas fa-info-circle"></i> Usarás este correo para iniciar sesión.
                </div>
            </div>

            <!-- Campo para la contraseña con opción para mostrar/ocultar -->
            <div class="mb-3">
                <label for="contraseña" class="form-label">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <div class="password-container">
                    <input type="password" class="form-control" id="contraseña" name="contraseña"
                        placeholder="Mínimo 8 caracteres" required minlength="8" aria-describedby="passwordHelp">
                    <span class="password-toggle" id="togglePassword" tabindex="0" role="button"
                        aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
                <div id="passwordHelp" class="form-text">
                    <i class="fas fa-info-circle"></i> Use al menos 8 caracteres, incluyendo letras, números y símbolos.
                </div>
            </div>

            <!-- Campo para seleccionar el rol del usuario -->
            <div class="mb-3">
                <label for="rol" class="form-label">
                    <i class="fas fa-user-tag"></i> Rol
                </label>
                <select class="form-control" id="rol" name="rol" required aria-describedby="rolHelp">
                    <option value="usuario" <?php echo ($rol_value === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                    <option value="admin" <?php echo ($rol_value === 'admin') ? 'selected' : ''; ?>>Administrador
                    </option>
                </select>
                <div id="rolHelp" class="form-text">
                    <i class="fas fa-info-circle"></i> Seleccione el tipo de cuenta que desea crear.
                </div>
            </div>

            <!-- Términos y condiciones -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terminos" required>
                <label class="form-check-label" for="terminos">
                    Acepto los <a href="#" title="Ver términos y condiciones">términos y condiciones</a>
                </label>
            </div>

            <!-- Botón para enviar el formulario -->
            <div class="d-grid">
                <button type="submit" class="btn-register" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>
            </div>

            <!-- Enlaces adicionales -->
            <div class="extra-links">
                <a href="login.php" title="Ir a la página de inicio de sesión">
                    <i class="fas fa-sign-in-alt"></i> ¿Ya tienes cuenta? Inicia Sesión
                </a>
                <a href="recuperar_contrasena.php" title="Recuperar contraseña olvidada">
                    <i class="fas fa-key"></i> ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>
    </div>

    <!-- Pie de página -->
    <footer>
        <p>© 2025 Sabor Colombiano - Diseñado con pasión.</p>
    </footer>

    <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap para interacciones dinámicas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script personalizado para validación y funcionalidades adicionales -->
    <script>
    // Esperar a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener referencias a los elementos del DOM
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('contraseña');
        const passwordStrength = document.getElementById('passwordStrength');
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');

        // Función para evaluar la fortaleza de la contraseña
        function checkPasswordStrength(password) {
            let strength = 0;

            // Si la contraseña tiene más de 7 caracteres, aumentar la fortaleza
            if (password.length >= 8) strength += 25;

            // Si la contraseña tiene letras minúsculas y mayúsculas, aumentar la fortaleza
            if (password.match(/[a-z]+/) && password.match(/[A-Z]+/)) strength += 25;

            // Si la contraseña tiene números, aumentar la fortaleza
            if (password.match(/[0-9]+/)) strength += 25;

            // Si la contraseña tiene caracteres especiales, aumentar la fortaleza
            if (password.match(/[^a-zA-Z0-9]+/)) strength += 25;

            return strength;
        }

        // Función para actualizar el indicador de fortaleza
        function updatePasswordStrength() {
            const password = passwordField.value;
            const strength = checkPasswordStrength(password);

            // Actualizar el color y ancho del indicador según la fortaleza
            passwordStrength.style.width = strength + '%';

            if (strength < 25) {
                passwordStrength.style.backgroundColor = '#f44336'; // Rojo - Muy débil
            } else if (strength < 50) {
                passwordStrength.style.backgroundColor = '#ff9800'; // Naranja - Débil
            } else if (strength < 75) {
                passwordStrength.style.backgroundColor = '#ffc107'; // Amarillo - Media
            } else {
                passwordStrength.style.backgroundColor = '#4caf50'; // Verde - Fuerte
            }
        }

        // Añadir evento de clic al icono de ojo para mostrar/ocultar contraseña
        togglePassword.addEventListener('click', function() {
            // Cambiar el tipo de campo entre 'password' y 'text'
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Cambiar el icono entre ojo y ojo tachado
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');

            // Actualizar el atributo aria-label para accesibilidad
            const ariaLabel = type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña';
            this.setAttribute('aria-label', ariaLabel);
        });

        // También permitir usar la tecla Enter en el icono para accesibilidad
        togglePassword.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });

        // Añadir evento de entrada al campo de contraseña para evaluar su fortaleza
        passwordField.addEventListener('input', updatePasswordStrength);

        // Validar el formulario antes de enviarlo
        registerForm.addEventListener('submit', function(e) {
            // Obtener valores de los campos
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const password = passwordField.value;
            const terminos = document.getElementById('terminos').checked;

            // Validar nombre
            if (nombre.length < 3) {
                e.preventDefault();
                alert('El nombre debe tener al menos 3 caracteres.');
                return;
            }

            // Validar correo con expresión regular
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(correo)) {
                e.preventDefault();
                alert('Por favor, ingrese un correo electrónico válido.');
                return;
            }

            // Validar contraseña
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                return;
            }

            // Validar términos y condiciones
            if (!terminos) {
                e.preventDefault();
                alert('Debe aceptar los términos y condiciones para continuar.');
                return;
            }

            // Cambiar el texto del botón para indicar que se está procesando
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            submitBtn.disabled = true;
        });
    });
    </script>
</body>

</html>