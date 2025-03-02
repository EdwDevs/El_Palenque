<?php
// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Obtener los datos del formulario
$correo = $_POST['correo'];
$contraseña = $_POST['contraseña'];

// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "root", "", "usuarios");

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Consulta para obtener el estado, nombre, rol y contraseña del usuario
$consulta = "SELECT nombre, rol, habilitado, contraseña FROM usuarios WHERE correo = ?";
$stmt = $conexion->prepare($consulta);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si existe el usuario
if ($resultado && mysqli_num_rows($resultado) > 0) {
    $user = mysqli_fetch_assoc($resultado);

    // Verificar la contraseña usando password_verify
    if (password_verify($contraseña, $user['contraseña'])) {
        // Verificar si el usuario está habilitado
        if ($user['habilitado'] == 0) {
            // Mostrar mensaje de usuario inhabilitado y redirigir después de 5 segundos
            echo "<script>
                alert('El usuario está inhabilitado. Por favor, contacta a un administrador con permisos.');
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 5000); // Redirige después de 5 segundos
            </script>";
            exit();
        }

        // Si el usuario está habilitado, iniciar sesión
        $_SESSION['correo'] = $correo;
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];

        // Redirigir según el rol
        if ($user['rol'] == 'admin') {
            header("Location: admin_home.php");
        } else {
            header("Location: user_home.php");
        }
        exit();
    } else {
        // Contraseña incorrecta
        echo "<script>alert('Contraseña incorrecta'); window.location.href='login.php';</script>";
    }
} else {
    // Correo no encontrado
    echo "<script>alert('Correo no registrado'); window.location.href='login.php';</script>";
}

// Cerrar recursos
$stmt->close();
$conexion->close();
?>