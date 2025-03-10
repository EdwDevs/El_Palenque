<?php
session_start();

// Verificar si el usuario está autenticado y si la estructura de la sesión es correcta
if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    echo "Sesión no válida o usuario no autenticado.";
    header("Location: login.php");
    exit();
} else {
    echo "Usuario autenticado: " . print_r($_SESSION['usuario'], true);
}

// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "root", "", "usuarios");

// Verificar la conexión
if (mysqli_connect_errno()) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

// Establecer el conjunto de caracteres a utf8
mysqli_set_charset($conexion, "utf8");

// Obtener datos del formulario
$usuario_id = $_SESSION['usuario']['id'];
$comentario = $_POST['comentario'] ?? '';

// Validar que el comentario no esté vacío
if (empty($comentario)) {
    echo "El comentario no puede estar vacío.";
    exit();
}

// Insertar el comentario en la base de datos
$stmt = mysqli_prepare($conexion, "INSERT INTO comentarios (usuario_id, comentario) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "is", $usuario_id, $comentario);

if (mysqli_stmt_execute($stmt)) {
    header("Location: historias_comunidad.php");
    exit();
} else {
    echo "Error al guardar el comentario: " . mysqli_stmt_error($stmt);
}

// Cerrar la declaración y la conexión
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
