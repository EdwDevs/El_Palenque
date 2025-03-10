<?php
session_start();
// Limpiar variables de sesión relacionadas con el pedido
unset($_SESSION['ultimo_pedido']);
unset($_SESSION['numero_pedido']);
echo "OK";
?>