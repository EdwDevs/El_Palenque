<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

include('db.php');

// Obtener los datos enviados desde el carrito
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['cart']) || empty($data['cart'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit();
}

// Obtener el ID del usuario
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;
if (!$usuario_id) {
    $usuario = htmlspecialchars($_SESSION['usuario']);
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?"); // Cambiar 'usuario' a 'nombre'
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $usuario_id = $row['id'];
        $_SESSION['usuario_id'] = $usuario_id; // Guardar en la sesión para evitar consultas futuras
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en la base de datos: ' . $usuario]);
        exit();
    }
    $stmt->close();
}

$cart = $data['cart'];
$total = 0;

foreach ($cart as $item) {
    $total += floatval($item['price']); // Total basado en precio unitario (asumimos cantidad 1 por ahora)
}

// Iniciar transacción para asegurar consistencia
$conexion->begin_transaction();

try {
    // Insertar el pedido con usuario_id
    $stmt = $conexion->prepare("INSERT INTO pedidos (usuario_id, total, fecha_pedido, estado) VALUES (?, ?, NOW(), 'pendiente')");
    $stmt->bind_param("id", $usuario_id, $total);
    $stmt->execute();
    $pedido_id = $conexion->insert_id;
    $stmt->close();

    // Insertar los detalles del pedido
    $stmt = $conexion->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $producto_id = intval($item['id']);
        $cantidad = 1; // Asumimos 1 unidad por producto; ajusta si necesitas manejar cantidades
        $precio_unitario = floatval($item['price']);
        $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio_unitario);
        $stmt->execute();
    }
    $stmt->close();

    // Confirmar transacción
    $conexion->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Pedido procesado correctamente']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conexion->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al procesar el pedido: ' . $e->getMessage()]);
}

$conexion->close();
?>