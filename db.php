<?php
// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "root", "", "usuarios");

// Verificar la conexión
if (mysqli_connect_errno()) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

// Establecer el conjunto de caracteres a utf8
mysqli_set_charset($conexion, "utf8");
?>