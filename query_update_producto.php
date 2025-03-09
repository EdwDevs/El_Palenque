<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria_id = intval($_POST['categoria_id']);
    $fecha_creacion = $_POST['fecha_creacion'];

    $stmt = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, fecha_creacion = ? WHERE id = ?");
    $stmt->bind_param("ssdisi", $nombre, $descripcion, $precio, $categoria_id, $fecha_creacion, $id);

    if ($stmt->execute()) {
        header("Location: productos.php?success=Producto actualizado");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conexion->close();
?>