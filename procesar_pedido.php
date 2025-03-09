<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes estar autenticado para realizar un pedido']);
    exit();
}

// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "el_palenque";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Recibir datos del pedido
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos
    if (!isset($data['usuario_id']) || !isset($data['productos']) || !isset($data['total'])) {
        echo json_encode(['success' => false, 'message' => 'Datos del pedido incompletos']);
        exit();
    }
    
    // Insertar pedido principal
    $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (:usuario_id, :total)");
    $stmt->bindParam(':usuario_id', $data['usuario_id']);
    $stmt->bindParam(':total', $data['total']);
    $stmt->execute();
    
    $pedido_id = $conn->lastInsertId();
    
    // Insertar detalles del pedido
    foreach ($data['productos'] as $producto) {
        $stmtDetalle = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)");
        
        // Aquí asumimos que el producto_id es el mismo que el id en el carrito
        // En una implementación real, deberías validar esto
        $stmtDetalle->bindParam(':pedido_id', $pedido_id);
        $stmtDetalle->bindParam(':producto_id', $producto['id']);
        $stmtDetalle->bindParam(':cantidad', 1); // Aquí asumimos 1 unidad por producto en el carrito
        $stmtDetalle->bindParam(':precio_unitario', $producto['price']);
        $stmtDetalle->execute();
    }
    
    // Respuesta de éxito
    echo json_encode(['success' => true, 'message' => '¡Pedido realizado con éxito!']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al procesar el pedido: ' . $e->getMessage()]);
}
?>