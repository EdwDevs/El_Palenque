<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Incluir la conexión a la base de datos
include('db.php');

// Variable para almacenar datos del usuario (con valores predeterminados)
$user = ['nombre' => '', 'correo' => ''];

// Obtener el nombre de usuario desde la sesión
$nombre_usuario = $_SESSION['usuario'];

// Variable para depuración
$debug_info = '';
$debug_mode = false; // Cambiar a false en producción

// Función para depuración
function debug_log($message) {
    global $debug_info, $debug_mode;
    if ($debug_mode) {
        $debug_info .= "<p><strong>DEBUG:</strong> " . htmlspecialchars($message) . "</p>";
    }
}

// Registrar información de depuración
debug_log("Usuario en sesión: " . $nombre_usuario);

// Verificar que la conexión a la base de datos existe
if (!isset($conexion) || $conexion->connect_error) {
    $error_msg = "Error de conexión a la base de datos. Por favor, contacte al administrador.";
    debug_log("Error de conexión: " . ($conexion->connect_error ?? 'Variable $conexion no definida'));
} else {
    try {
        // Consulta modificada para buscar por nombre en lugar de correo
        $stmt = $conexion->prepare("SELECT nombre, correo FROM usuarios WHERE nombre = ?");
        if ($stmt) {
            $stmt->bind_param("s", $nombre_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            debug_log("Consulta 1: SELECT nombre, correo FROM usuarios WHERE nombre = '$nombre_usuario'");
            debug_log("Filas encontradas: " . $result->num_rows);
            
            // Verificar si se encontró el usuario
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                debug_log("Usuario encontrado: " . print_r($user, true));
            } else {
                // Si no se encuentra, intentamos buscar ignorando mayúsculas/minúsculas
                $stmt2 = $conexion->prepare("SELECT nombre, correo FROM usuarios WHERE LOWER(nombre) = LOWER(?)");
                $stmt2->bind_param("s", $nombre_usuario);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                
                debug_log("Consulta 2: SELECT nombre, correo FROM usuarios WHERE LOWER(nombre) = LOWER('$nombre_usuario')");
                debug_log("Filas encontradas: " . $result2->num_rows);
                
                if ($result2->num_rows > 0) {
                    $user = $result2->fetch_assoc();
                    debug_log("Usuario encontrado (ignorando mayúsculas/minúsculas): " . print_r($user, true));
                } else {
                    // Mostrar todos los usuarios para depuración
                    $all_users = $conexion->query("SELECT id, nombre, correo FROM usuarios LIMIT 10");
                    $users_list = "";
                    if ($all_users && $all_users->num_rows > 0) {
                        while ($row = $all_users->fetch_assoc()) {
                            $users_list .= "ID: " . $row['id'] . ", Nombre: " . $row['nombre'] . ", Correo: " . $row['correo'] . "<br>";
                        }
                        debug_log("Usuarios disponibles en la base de datos (primeros 10): <br>" . $users_list);
                    } else {
                        debug_log("No se encontraron usuarios en la tabla.");
                    }
                    
                    // Mensaje de error para el usuario
                    $error_msg = "No se encontró información para este usuario. Por favor, contacte al administrador.";
                }
            }
        } else {
            $error_msg = "Error al preparar la consulta: " . $conexion->error;
            debug_log("Error en prepare: " . $conexion->error);
        }
    } catch (Exception $e) {
        $error_msg = "Error al buscar los datos del usuario: " . $e->getMessage();
        debug_log("Excepción: " . $e->getMessage());
    }
}

// Manejo del formulario de actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
    $nuevo_correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

    // Validar campos
    if (empty($nuevo_nombre) || empty($nuevo_correo)) {
        $error_msg = "Todos los campos son obligatorios.";
    } elseif (!filter_var($nuevo_correo, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Por favor, ingrese un correo electrónico válido.";
    } else {
        try {
            // Comprobar si el nuevo correo ya existe (solo si es diferente al actual)
            if ($nuevo_correo != ($user['correo'] ?? '')) {
                $check_stmt = $conexion->prepare("SELECT COUNT(*) as count FROM usuarios WHERE correo = ? AND nombre != ?");
                $check_stmt->bind_param("ss", $nuevo_correo, $nombre_usuario);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_row = $check_result->fetch_assoc();
                
                if ($check_row['count'] > 0) {
                    $error_msg = "Este correo electrónico ya está en uso. Por favor, elija otro.";
                } else {
                    // Actualizar los datos en la base de datos
                    $update_stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE nombre = ?");
                    $update_stmt->bind_param("sss", $nuevo_nombre, $nuevo_correo, $nombre_usuario);

                    if ($update_stmt->execute()) {
                        // Actualizar la sesión con el nuevo nombre si ha cambiado
                        if ($nuevo_nombre != $nombre_usuario) {
                            $_SESSION['usuario'] = $nuevo_nombre;
                        }
                        $success_msg = "Perfil actualizado correctamente.";
                        // Actualizar el array de usuario para mostrar los nuevos valores
                        $user['nombre'] = $nuevo_nombre;
                        $user['correo'] = $nuevo_correo;
                    } else {
                        $error_msg = "Error al actualizar el perfil: " . $conexion->error;
                    }
                }
            } else {
                // Si el correo no ha cambiado, solo actualizar el nombre
                $update_stmt = $conexion->prepare("UPDATE usuarios SET nombre = ? WHERE nombre = ?");
                $update_stmt->bind_param("ss", $nuevo_nombre, $nombre_usuario);

                if ($update_stmt->execute()) {
                    // Actualizar la sesión con el nuevo nombre si ha cambiado
                    if ($nuevo_nombre != $nombre_usuario) {
                        $_SESSION['usuario'] = $nuevo_nombre;
                    }
                    $success_msg = "Perfil actualizado correctamente.";
                    // Actualizar el array de usuario para mostrar los nuevos valores
                    $user['nombre'] = $nuevo_nombre;
                } else {
                    $error_msg = "Error al actualizar el perfil: " . $conexion->error;
                }
            }
        } catch (Exception $e) {
            $error_msg = "Error al actualizar los datos: " . $e->getMessage();
            debug_log("Excepción en actualización: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Actualiza tus datos de perfil en Sabor Colombiano">
    <title>Editar Perfil - Sabor Colombiano</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        /* Estilos globales */
        body {
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: var(--color-text);
            margin: 0;
            padding: 0;
            position: relative;
            padding-bottom: 60px; /* Espacio para el footer */
        }

        /* Header y navegación */
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
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            box-sizing: border-box;
            transition: var(--transition-normal);
        }
        
        header.scrolled {
            padding: 0.5rem 2rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .header-logo {
            flex-shrink: 0;
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
        }

        .btn-auth:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
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

        /* Contenido principal */
        .main-content {
            padding: 10rem 2rem 5rem;
            max-width: 800px;
            margin: 0 auto;
            animation: fadeIn 1s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .profile-card h2 {
            color: var(--color-primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
            text-align: center;
        }
        
        .profile-card h2::after {
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

        .form-label {
            color: var(--color-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            transition: var(--transition-normal);
        }

        .form-control:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        }

        .btn-submit {
            background-color: var(--color-secondary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 1rem;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .button-group {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        /* Depuración */
        .debug-container {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
        }
        
        .debug-container h3 {
            color: #6c757d;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .debug-container pre {
            background-color: #343a40;
            color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
        }

        /* Footer */
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

        /* Responsive */
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
            
            .main-content {
                padding: 8rem 1.5rem 3rem;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links, .social-icons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="main-header">
        <div class="header-logo">
            <a href="index.php" title="Página de inicio">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>
        
        <nav class="nav-links" aria-label="Navegación principal">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="user_home.php">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#" aria-current="page">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="auth-container">
            <span class="user-welcome">
                <i class="fas fa-user"></i> Hola, <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Usuario'); ?>
            </span>
            <a href="logout.php" title="Cerrar sesión">
                <button class="btn-auth">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </button>
            </a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="profile-card">
            <h2>Editar Perfil</h2>
            
            <?php if (isset($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="profileForm">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre'] ?? $_SESSION['usuario'] ?? ''); ?>" required placeholder="Tu nombre completo">
                </div>
                
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($user['correo'] ?? ''); ?>" required placeholder="tu@email.com">
                </div>
                
                <div class="button-group">
                    <a href="user_home.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($debug_mode && !empty($debug_info)): ?>
        <div class="debug-container">
            <h3>Información de depuración</h3>
            <div class="debug-content">
                <?php echo $debug_info; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personalizado -->
    <script>
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
            
            // Confirmación antes de enviar el formulario
            const profileForm = document.getElementById('profileForm');
            profileForm.addEventListener('submit', function(e) {
                const currentName = "<?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?>";
                const newName = document.getElementById('nombre').value;
                
                if (newName !== currentName) {
                    if (!confirm('¿Estás seguro de que deseas cambiar tu nombre de usuario? Tendrás que usar el nuevo nombre para iniciar sesión.')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>