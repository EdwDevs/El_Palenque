<?php
// registro_usuario_bd.php
// Incluir la conexión a la base de datos
include('db.php');

if (isset($_GET['nombre']) && isset($_GET['correo']) && isset($_GET['contraseña']) && isset($_GET['rol'])) {
    // Obtener los datos recibidos desde la URL y decodificarlos
    $nombre = urldecode($_GET['nombre']);
    $correo = urldecode($_GET['correo']);
    $contraseña = urldecode($_GET['contraseña']); // En producción, hashea esta contraseña con password_hash()
    $rol = urldecode($_GET['rol']);

    // Verificar si el correo ya existe en la base de datos usando una consulta preparada
    $check_sql = "SELECT COUNT(*) FROM usuarios WHERE correo = ?";
    $check_stmt = $conexion->prepare($check_sql);
    if (!$check_stmt) {
        die("Error al preparar la consulta de verificación: " . $conexion->error);
    }
    $check_stmt->bind_param("s", $correo);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    // Si el correo ya existe, mostrar error y redirigir
    if ($count > 0) {
        echo "<script>alert('El correo $correo ya está en uso. Por favor, usa otro correo.'); window.location.href='register.php';</script>";
        exit;
    }

    // Insertar el usuario en la base de datos usando una consulta preparada para seguridad
    $sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol, habilitado) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }
    $stmt->bind_param("ssss", $nombre, $correo, $contraseña, $rol);

    if ($stmt->execute()) {
        // Verificar si se indica éxito en la redirección
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            // Generar una página HTML básica para mostrar la alerta de éxito y redirigir
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Registro Exitoso - Sabor Colombiano</title>
                <!-- Bootstrap CSS: Incluye los estilos de Bootstrap para un diseño responsive -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Google Fonts - Montserrat: Añade tipografía profesional -->
                <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
                <style>
                    body {
                        background: linear-gradient(135deg, #FFC107, #FF5722, #4CAF50);
                        min-height: 100vh;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        font-family: 'Montserrat', sans-serif;
                        color: #333;
                    }
                    .success-container {
                        background: rgba(255, 255, 255, 0.95);
                        padding: 2.5rem;
                        border-radius: 20px;
                        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
                        max-width: 450px;
                        margin: 0 auto;
                        text-align: center;
                        border: 1px solid #FFC107;
                    }
                    h2 {
                        color: #4CAF50;
                        font-weight: 700;
                        font-size: 2rem;
                        margin-bottom: 1.5rem;
                    }
                    p {
                        color: #333;
                        font-size: 1.2rem;
                        margin-bottom: 1.5rem;
                    }
                </style>
            </head>
            <body>
                <div class="success-container">
                    <h2>¡Registro Exitoso!</h2>
                    <p>Usuario registrado con éxito:<br>Nombre: <?php echo htmlspecialchars($nombre); ?><br>Correo: <?php echo htmlspecialchars($correo); ?></p>
                    <script>
                        // Mostrar alerta y redirigir después de 3 segundos
                        setTimeout(function() {
                            alert('Usuario registrado con éxito:\nNombre: <?php echo addslashes($nombre); ?>\nCorreo: <?php echo addslashes($correo); ?>');
                            window.location.href = 'login.php';
                        }, 3000); // Espera 3 segundos antes de mostrar la alerta y redirigir
                    </script>
                </div>
                <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap para interacciones dinámicas -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php
            exit;
        } else {
            // Redirigir al login sin notificación si no se indica éxito
            header("Location: login.php");
            exit;
        }
    } else {
        // Mostrar error si falla la inserción y redirigir al registro
        echo "<script>alert('Error al registrar el usuario'); window.location.href='register.php';</script>";
        exit;
    }

    // Cerrar el statement y la conexión
    $stmt->close();
    $conexion->close();
} else {
    // Si no se reciben todos los datos necesarios, redirigir al formulario de registro
    echo "<script>alert('No se recibieron todos los datos necesarios'); window.location.href='register.php';</script>";
    exit;
}
?>