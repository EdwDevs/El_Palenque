<?php
include('db.php');

// Verificar que se haya proporcionado un correo
if (!isset($_GET['correo']) || empty($_GET['correo'])) {
    die("Error: No se proporcionó el correo del usuario.");
}

$correo = $_GET['correo'];

// Preparar y ejecutar la actualización para habilitar el usuario usando el correo
$sql = "UPDATE usuarios SET habilitado = 1 WHERE correo = ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $correo);

if ($stmt->execute()) {
    // Verificar si se actualizó alguna fila
    if ($stmt->affected_rows > 0) {
        header("Location: admin_home.php"); // Redirigir si la actualización fue exitosa
        exit;
    } else {
        echo "No se pudo habilitar el usuario con correo $correo. Posiblemente ya estaba habilitado o el correo no existe.";
    }
} else {
    echo "Error al habilitar el usuario: " . $stmt->error;
}

// Cerrar recursos
$stmt->close();
$conexion->close();
?>