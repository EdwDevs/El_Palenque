<?php
// Guardar como test_procesar.php
// Este archivo simplemente muestra los datos recibidos y redirige

// Iniciar sesión
session_start();

// Habilitar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mostrar todos los datos recibidos
echo "<h1>Datos recibidos:</h1>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Guardar en sesión para simular el proceso
$_SESSION['ultimo_pedido'] = 123;
$_SESSION['numero_pedido'] = "PED-TEST-123";

// Esperar 5 segundos y redirigir
echo "<p>Redirigiendo en 5 segundos...</p>";
echo "<script>setTimeout(function() { window.location.href = 'confirmacion_pedido.php'; }, 5000);</script>";
?>