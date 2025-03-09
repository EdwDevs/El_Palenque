<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria_id = intval($_POST['categoria_id']);
    $fecha_creacion = $_POST['fecha_creacion'];

    error_log("Datos recibidos: nombre=$nombre, descripcion=$descripcion, precio=$precio, categoria_id=$categoria_id, fecha_creacion=$fecha_creacion");

    $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id, fecha_creacion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $nombre, $descripcion, $precio, $categoria_id, $fecha_creacion);

    if ($stmt->execute()) {
        header("Location: productos.php?success=Producto guardado correctamente");
        exit();
    } else {
        error_log("Error al guardar: " . $stmt->error);
        header("Location: productos.php?error=Error al guardar el producto: " . urlencode($stmt->error));
        exit();
    }

    $stmt->close();
}

$conexion->close();
?>