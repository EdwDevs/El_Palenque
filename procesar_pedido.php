<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?mensaje=Usuario no autenticado");
    exit();
}

include('db.php');

// Verificar que sea una solicitud POST desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: carrito.php?mensaje=Método no permitido");
    exit();
}

// Obtener los datos del formulario
$cart = json_decode($_POST['cart'] ?? '', true);
$forma_pago = $_POST['forma_pago'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$telefono = $_POST['telefono'] ?? '';

if (empty($cart) || empty($forma_pago) || empty($direccion) || empty($ciudad) || empty($telefono)) {
    header("Location: finalizar_compra.php?mensaje=Datos incompletos");
    exit();
}

// Obtener el ID del usuario
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;
if (!$usuario_id) {
    $usuario = htmlspecialchars($_SESSION['usuario']);
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $usuario_id = $row['id'];
        $_SESSION['usuario_id'] = $usuario_id;
    } else {
        header("Location: carrito.php?mensaje=Usuario no encontrado");
        exit();
    }
    $stmt->close();
}

// Calcular el total
$total = 0;
foreach ($cart as $item) {
    $total += floatval($item['price']) * (isset($item['cantidad']) ? $item['cantidad'] : 1);
}

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Insertar el pedido
    $stmt = $conexion->prepare("INSERT INTO pedidos (usuario_id, total, fecha_pedido, estado) VALUES (?, ?, NOW(), 'pendiente')");
    $stmt->bind_param("id", $usuario_id, $total);
    $stmt->execute();
    $pedido_id = $conexion->insert_id;
    $stmt->close();

    // Insertar detalles del pedido
    $stmt = $conexion->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $producto_id = intval($item['id']);
        $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 1;
        $precio_unitario = floatval($item['price']);
        $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio_unitario);
        $stmt->execute();
    }
    $stmt->close();

    // Insertar datos de entrega
    $stmt = $conexion->prepare("INSERT INTO entregas (pedido_id, forma_pago, direccion, ciudad, telefono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $pedido_id, $forma_pago, $direccion, $ciudad, $telefono);
    $stmt->execute();
    $stmt->close();

    // Confirmar transacción
    $conexion->commit();

    // Redirigir a index.php con mensaje y clear_cart
    header("Location: index.php?mensaje=Pedido confirmado correctamente&clear_cart=1");
    exit();
} catch (Exception $e) {
    $conexion->rollback();
    header("Location: finalizar_compra.php?mensaje=Error al procesar el pedido: " . urlencode($e->getMessage()));
    exit();
}

$conexion->close();
?>