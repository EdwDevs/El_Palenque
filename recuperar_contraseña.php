<?php
// Incluir la conexión a la base de datos
include('db.php');

// Iniciar un log para depuración
error_log("Inicio de recuperar_contraseña.php - " . date('Y-m-d H:i:s'));

// Verificar si se recibió un correo por método POST desde el formulario
if (isset($_POST['correo'])) {
    $correo = $_POST['correo'];

    // Depuración: Mostrar el correo recibido
    error_log("Correo recibido: $correo");

    // Verificar si el correo existe en la tabla 'usuarios' usando una consulta preparada para seguridad
    $consulta = "SELECT id, nombre FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($consulta);
    if (!$stmt) {
        error_log("Error al preparar la consulta para verificar el correo: " . $conexion->error);
        die("Error al preparar la consulta para verificar el correo: " . $conexion->error);
    }
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Depuración: Mostrar el resultado de la consulta
    error_log("Resultado de la consulta para correo $correo: " . ($resultado->num_rows > 0 ? "Usuario encontrado" : "Usuario no encontrado"));

    // Comprobar si se encontró el correo en la base de datos
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $token = bin2hex(random_bytes(16)); // Generar un token único de 32 caracteres hexadecimales
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Establecer la expiración del token a 1 hora

        // Depuración: Mostrar datos del usuario y token
        error_log("Usuario encontrado - Nombre: " . $usuario['nombre'] . ", Token: $token, Expiración: $expiracion");

        // Verificar si la tabla password_resets existe, si no, crearla
        $check_table = "SHOW TABLES LIKE 'password_resets'";
        $table_exists = $conexion->query($check_table);
        
        if ($table_exists->num_rows == 0) {
            // Crear la tabla si no existe
            $create_table = "CREATE TABLE password_resets (
                id INT(11) NOT NULL AUTO_INCREMENT,
                correo VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expiracion DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            )";
            
            if (!$conexion->query($create_table)) {
                error_log("Error al crear la tabla password_resets: " . $conexion->error);
                die("Error al crear la tabla password_resets: " . $conexion->error);
            }
            
            error_log("Tabla password_resets creada exitosamente");
        }

        // Eliminar tokens anteriores para este correo
        $delete_old = "DELETE FROM password_resets WHERE correo = ?";
        $stmt_delete = $conexion->prepare($delete_old);
        if (!$stmt_delete) {
            error_log("Error al preparar la eliminación de tokens antiguos: " . $conexion->error);
        } else {
            $stmt_delete->bind_param("s", $correo);
            $stmt_delete->execute();
            $stmt_delete->close();
            error_log("Tokens antiguos eliminados para el correo $correo");
        }

        // Guardar el token y la expiración en la tabla 'password_resets'
        $sql = "INSERT INTO password_resets (correo, token, expiracion) VALUES (?, ?, ?)";
        $stmt_insert = $conexion->prepare($sql);
        if (!$stmt_insert) {
            error_log("Error al preparar la inserción del token: " . $conexion->error);
            die("Error al preparar la inserción del token: " . $conexion->error);
        }
        $stmt_insert->bind_param("sss", $correo, $token, $expiracion);
        $stmt_insert->execute();

        // Depuración: Verificar si la inserción fue exitosa
        error_log("Inserción en password_resets exitosa para correo $correo, token $token");

        // Configurar PHPMailer para enviar el correo de recuperación
        // Cargar las clases de PHPMailer directamente
        require_once __DIR__ . '/src/PHPMailer.php';
        require_once __DIR__ . '/src/SMTP.php';
        require_once __DIR__ . '/src/Exception.php';

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages
            
            // Configuración para Mailtrap con tus credenciales
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'c6ed680deabbaf'; // Tu username de Mailtrap
            $mail->Password = 'ca4d7fbd44c6b0'; // Tu password de Mailtrap
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525; // Puerto de Mailtrap
            
            // Configuración del remitente y destinatario
            $mail->setFrom('noreply@saborcolombiano.com', 'Sabor Colombiano');
            $mail->addAddress($correo, $usuario['nombre']);
            
            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Recuperar Contraseña - Sabor Colombiano';
            
            // URL base del sitio (ajusta según tu configuración)
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            // CAMBIO AQUÍ: Actualización del nombre del archivo en la URL
            $reset_url = $base_url . "/usuarios_roles/usuarios_roles/restablecer_contrasena.php?token=" . $token;
            
            // Cuerpo del correo en HTML para mejor presentación
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; }
                    .button { display: inline-block; background-color: #FF5722; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Recuperación de Contraseña</h2>
                    </div>
                    <div class='content'>
                        <p>Hola <strong>{$usuario['nombre']}</strong>,</p>
                        <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en Sabor Colombiano.</p>
                        <p>Para continuar con el proceso, haz clic en el siguiente botón:</p>
                        <p style='text-align: center;'>
                            <a href='$reset_url' class='button'>Restablecer Contraseña</a>
                        </p>
                        <p>O copia y pega el siguiente enlace en tu navegador:</p>
                        <p>$reset_url</p>
                        <p>Este enlace expirará en 1 hora por razones de seguridad.</p>
                        <p>Si no solicitaste este cambio, puedes ignorar este correo y tu contraseña permanecerá sin cambios.</p>
                    </div>
                    <div class='footer'>
                        <p>© 2025 Sabor Colombiano - Todos los derechos reservados.</p>
                        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Versión en texto plano para clientes de correo que no soportan HTML
            $mail->AltBody = "Hola {$usuario['nombre']},\n\n" .
                            "Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en Sabor Colombiano.\n\n" .
                            "Para continuar con el proceso, copia y pega el siguiente enlace en tu navegador:\n\n" .
                            "$reset_url\n\n" .
                            "Este enlace expirará en 1 hora por razones de seguridad.\n\n" .
                            "Si no solicitaste este cambio, puedes ignorar este correo y tu contraseña permanecerá sin cambios.\n\n" .
                            "© 2025 Sabor Colombiano";

            // Intentar enviar el correo
            if ($mail->send()) {
                error_log("Correo enviado con éxito a $correo");
                echo "<script>
                    alert('Se ha enviado un enlace de recuperación a tu correo electrónico. Por favor, revisa tu bandeja de entrada.');
                    window.location.href='login.php';
                </script>";
            } else {
                error_log("Error al enviar el correo: " . $mail->ErrorInfo);
                throw new Exception("Error al enviar el correo: " . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            error_log("Excepción capturada: " . $e->getMessage());
            echo "<script>
                alert('Error al enviar el correo de recuperación. Por favor, intenta nuevamente más tarde o contacta al administrador.');
                window.location.href='recuperar_contraseña.php';
            </script>";
        }
    } else {
        // Si el correo no existe, mostrar mensaje de error y redirigir
        error_log("Correo $correo no encontrado en la base de datos");
        echo "<script>
            alert('El correo electrónico ingresado no está registrado en nuestro sistema.');
            window.location.href='recuperar_contraseña.php';
        </script>";
    }

    // Cerrar los statements y la conexión para liberar recursos
    $stmt->close();
    if (isset($stmt_insert)) $stmt_insert->close();
    $conexion->close();
    error_log("Fin de recuperar_contraseña.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sabor Colombiano</title>
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
        
        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            text-align: center;
            left: 0;
        }
        
        header img {
            max-width: 120px;
            border-radius: 10px;
            border: 3px solid #FF5722;
            transition: transform 0.3s ease;
        }
        
        header img:hover {
            transform: scale(1.05);
        }
        
        /* Contenedor principal */
        .recovery-container {
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
        
        /* Descripción */
        .recovery-description {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #555;
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
        
        .input-group-text {
            background-color: #FFC107;
            border: 2px solid #FFC107;
            color: #333;
            border-radius: 0 10px 10px 0;
        }
        
        /* Botón */
        .btn-recovery {
            background-color: #4CAF50;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .btn-recovery:hover {
            background-color: #FF5722;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Enlaces */
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-to-login a {
            color: #FF5722;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .back-to-login a:hover {
            color: #4CAF50;
            text-decoration: underline;
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 1rem;
            color: #333;
            font-size: 0.9rem;
            margin-top: 2rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Header con logo -->
    <header>
        <a href="home.php">
            <img src="palenque.jpeg" alt="Palenquera Colombiana">
        </a>
    </header>

    <div class="recovery-container">
        <!-- Título del formulario -->
        <h2>Recuperar Contraseña</h2>
        
        <!-- Descripción del proceso -->
        <div class="recovery-description">
            <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
        </div>
        
        <!-- Formulario de recuperación -->
        <form action="recuperar_contraseña.php" method="post">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese su correo" required>
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn-recovery">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Enlace de Recuperación
                </button>
            </div>
        </form>
        
        <!-- Enlace para volver al login -->
        <div class="back-to-login">
            <a href="login.php"><i class="fas fa-arrow-left me-1"></i> Volver a Iniciar Sesión</a>
        </div>
    </div>

    <footer>
        <p>© 2025 Sabor Colombiano - Diseñado con pasión.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>