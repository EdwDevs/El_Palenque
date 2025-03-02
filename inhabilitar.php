<?php
include('db.php');

// Verificar que se haya proporcionado un correo
if (!isset($_GET['correo']) || empty($_GET['correo'])) {
    die("Error: No se proporcionó el correo del usuario.");
}

$correo = $_GET['correo'];

// Verificar la conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si el usuario existe
$check = $conexion->query("SELECT habilitado FROM usuarios WHERE correo = '$correo'");
if ($check->num_rows == 0) {
    echo "Usuario con correo $correo no encontrado.";
    exit;
}

$row = $check->fetch_assoc();
if ($row['habilitado'] == 0) {
    echo "El usuario con correo $correo ya está inhabilitado.";
    exit;
}

// Preparar y ejecutar la actualización usando el correo
$sql = "UPDATE usuarios SET habilitado = 0 WHERE correo = ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $correo);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: admin_home.php"); // Redirigir si la actualización fue exitosa
        exit;
    } else {
        echo "No se pudo inhabilitar el usuario con correo $correo. Posiblemente ya estaba inhabilitado.";
    }
} else {
    echo "Error al ejecutar la consulta: " . $stmt->error;
}

// Cerrar recursos
$stmt->close();
$conexion->close();
?>