<?php
// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// Incluir la conexión a la base de datos desde db.php
include('db.php');

// Obtener los datos del formulario
$correo = $_POST['correo'] ?? '';
$contraseña = $_POST['contraseña'] ?? '';

// Validar que los campos no estén vacíos
if (empty($correo) || empty($contraseña)) {
    echo "<script>alert('Por favor, completa todos los campos'); window.location.href='login.php';</script>";
    exit();
}

// Consulta para obtener id, nombre, rol, habilitado y contraseña del usuario
$consulta = "SELECT id, nombre, rol, habilitado, contraseña FROM usuarios WHERE correo = ?";
$stmt = $conexion->prepare($consulta);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si existe el usuario
if ($resultado && $resultado->num_rows > 0) {
    $user = $resultado->fetch_assoc();

    // Verificar la contraseña usando password_verify
    if (password_verify($contraseña, $user['contraseña'])) {
        // Verificar si el usuario está habilitado
        if ($user['habilitado'] == 0) {
            echo "<script>
                alert('El usuario está inhabilitado. Por favor, contacta a un administrador.');
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 5000);
            </script>";
            exit();
        }

        // Si el usuario está habilitado, iniciar sesión
        $_SESSION['correo'] = $correo;
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['usuario_id'] = $user['id']; // Guardar el ID del usuario
        $_SESSION['rol'] = $user['rol'];

        // Redirigir según el rol
        if ($user['rol'] === 'admin') {
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