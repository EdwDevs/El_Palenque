<?php
// Incluir la conexión a la base de datos
include('db.php');

// Iniciar un log para depuración
error_log("Inicio de restablecer_contraseña.php - " . date('Y-m-d H:i:s'));

// Verificar si se recibió un token válido
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verificar si el token existe y no ha expirado
    $consulta = "SELECT * FROM password_resets WHERE token = ? AND expiracion > NOW()";
    $stmt = $conexion->prepare($consulta);
    if (!$stmt) {
        error_log("Error al preparar la consulta para verificar el token: " . $conexion->error);
        die("Error al preparar la consulta para verificar el token: " . $conexion->error);
    }
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    // Si el token no es válido o ha expirado, redirigir al usuario
    if ($resultado->num_rows === 0) {
        error_log("Token inválido o expirado: $token");
        echo "<script>
            alert('El enlace de recuperación no es válido o ha expirado. Por favor, solicita uno nuevo.');
            window.location.href='recuperar_contraseña.php';
        </script>";
        exit();
    }
    
    // Si el token es válido, obtener el correo asociado
    $reset_data = $resultado->fetch_assoc();
    $correo = $reset_data['correo'];
    
    // Procesar el formulario de restablecimiento de contraseña
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_contraseña'])) {
        $nueva_contraseña = $_POST['nueva_contraseña'];
        $confirmar_contraseña = $_POST['confirmar_contraseña'];
        
        // Verificar que las contraseñas coincidan
        if ($nueva_contraseña !== $confirmar_contraseña) {
            echo "<script>alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');</script>";
        } else {
            // Hashear la nueva contraseña (recomendado para producción)
            $hashed_password = password_hash($nueva_contraseña, PASSWORD_BCRYPT);

            // Actualizar la contraseña en la base de datos
            $actualizar = "UPDATE usuarios SET contraseña = ? WHERE correo = ?";
            $stmt_update = $conexion->prepare($actualizar);
            if (!$stmt_update) {
                error_log("Error al preparar la actualización de contraseña: " . $conexion->error);
                die("Error al preparar la actualización de contraseña: " . $conexion->error);
            }
            $stmt_update->bind_param("ss", $hashed_password, $correo);
            $stmt_update->execute();
            
            // Eliminar el token usado
            $eliminar = "DELETE FROM password_resets WHERE token = ?";
            $stmt_delete = $conexion->prepare($eliminar);
            $stmt_delete->bind_param("s", $token);
            $stmt_delete->execute();
            
            // Redirigir al usuario al login con mensaje de éxito
            echo "<script>
                alert('Tu contraseña ha sido actualizada correctamente. Ahora puedes iniciar sesión con tu nueva contraseña.');
                window.location.href='login.php';
            </script>";
            exit();
        }
    }
} else {
    // Si no hay token, redirigir al usuario
    error_log("Intento de acceso a restablecer_contraseña.php sin token");
    echo "<script>
        alert('Acceso inválido. Por favor, solicita un enlace de recuperación de contraseña.');
        window.location.href='recuperar_contraseña.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sabor Colombiano</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos globales */
        body {
            background: linear-gradient(135deg, #FFC107, #FF5722, #4CAF50);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            color: #333;
            padding: 20px;
        }
        
        /* Contenedor principal */
        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            max-width: 450px;
            margin: 0 auto;
            border: 1px solid #FFC107;
            margin-top: 80px;
        }
        
        /* Título */
        h2 {
            color: #FF5722;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        /* Formulario */
        .form-label {
            color: #4CAF50;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .form-control {
            border: 2px solid #FFC107;
            border-radius: 10px;
            padding: 0.75rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            border-color: #FF5722;
            box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
            outline: none;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #4CAF50;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #FF5722;
        }
        
        /* Botón */
        .btn-reset {
            background-color: #4CAF50;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .btn-reset:hover {
            background-color: #FF5722;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Título del formulario -->
        <h2>Restablecer Contraseña</h2>
        
        <!-- Formulario de restablecimiento -->
        <form action="restablecer_contrasena.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <div class="mb-3">
                <label for="nueva_contraseña" class="form-label">Nueva Contraseña</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña" placeholder="Ingresa tu nueva contraseña" required>
                    <span class="password-toggle" id="togglePassword1">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirmar_contraseña" class="form-label">Confirmar Contraseña</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña" placeholder="Confirma tu nueva contraseña" required>
                    <span class="password-toggle" id="togglePassword2">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn-reset">Cambiar Contraseña</button>
            </div>
        </form>
    </div>

    <!-- Script para mostrar/ocultar contraseñas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword1 = document.getElementById('togglePassword1');
            const passwordField1 = document.getElementById('nueva_contraseña');
            togglePassword1.addEventListener('click', function() {
                const type = passwordField1.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField1.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            const togglePassword2 = document.getElementById('togglePassword2');
            const passwordField2 = document.getElementById('confirmar_contraseña');
            togglePassword2.addEventListener('click', function() {
                const type = passwordField2.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField2.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>