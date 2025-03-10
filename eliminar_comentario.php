<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: user_home.php");
    exit();
}

include('db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $section = isset($_GET['section']) ? $_GET['section'] : 'comments';
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_home.php?section=" . $section);
    exit();
}
