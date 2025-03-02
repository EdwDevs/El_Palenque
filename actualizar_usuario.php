<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];

    // Consulta SQL para actualizar solo el nombre y correo
    $sql = "UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $correo, $id);

    if ($stmt->execute()) {
        header("Location: admin_home.php"); // Redirige a la página de gestión
        exit;
    } else {
        echo "Error al actualizar el usuario.";
    }
    $stmt->close();
}
$conexion->close();
?>