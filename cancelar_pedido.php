<?php
// cancelar_pedido.php - Permite a los usuarios cancelar un pedido pendiente
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

include('db.php');

// Verificar si se proporcionó un ID de pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: mis_pedidos.php?mensaje=ID de pedido no válido&status=error");
    exit();
}

$pedido_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que el pedido pertenezca al usuario actual (o es admin)
$es_admin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

if (!$es_admin) {
    $stmt = $conexion->prepare("SELECT estado FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $pedido_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: mis_pedidos.php?mensaje=No tienes permiso para cancelar este pedido&status=error");
        exit();
    }
    
    $row = $result->fetch_assoc();
    if ($row['estado'] != 'pendiente') {
        header("Location: mis_pedidos.php?mensaje=Solo se pueden cancelar pedidos pendientes&status=error");
        exit();
    }
    
    $stmt->close();
}

// Cancelar el pedido
$stmt = $conexion->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
$stmt->bind_param("i", $pedido_id);
$resultado = $stmt->execute();
$stmt->close();

if ($resultado) {
    header("Location: mis_pedidos.php?mensaje=Pedido cancelado correctamente&status=success");
} else {
    header("Location: mis_pedidos.php?mensaje=Error al cancelar el pedido&status=error");
}
exit();
?>