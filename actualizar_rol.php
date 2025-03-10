<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $rol = $_POST['rol'];

    // Actualizar el rol en la base de datos
    $update = $conexion->query("UPDATE usuarios SET rol = '$rol' WHERE id = $id");

    if ($update) {
        // Redirigir a admin_home.php después de actualizar
        header("Location: admin_home.php");
        exit;
    } else {
        echo "Error al actualizar el rol: " . $conexion->error;
    }
} else {
    echo "Método no permitido.";
}
