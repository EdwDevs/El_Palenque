<?php
// procesar_pedido.php - Versión final adaptada a tu estructura
session_start();
require_once 'db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?redirect=finalizar_compra.php');
    exit;
}

// Verificar si se recibieron datos del formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit;
}

// Obtener datos del formulario
$usuario_id = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$codigo_postal = $_POST['codigo_postal'] ?? '';
$tipo_envio = $_POST['tipo_envio'] ?? 'estandar';
$metodo_pago = $_POST['metodo_pago'] ?? '';
$notas = $_POST['notas'] ?? '';

// Obtener productos del carrito
$cart = [];
if (isset($_POST['cart']) && !empty($_POST['cart'])) {
    $cart = json_decode($_POST['cart'], true);
} else {
    // Si no hay carrito en POST, intentar obtenerlo de la sesión
    $cart = $_SESSION['carrito'] ?? [];
}

// Verificar si el carrito está vacío
if (empty($cart)) {
    $_SESSION['error'] = "Tu carrito está vacío. Agrega productos antes de finalizar la compra.";
    header('Location: carrito.php');
    exit;
}

// Calcular costos
$subtotal = 0;
$impuestos = 0;
$envio = 5000; // Costo base de envío

if ($tipo_envio === 'express') {
    $envio += 10000;
}

// Calcular subtotal e impuestos
foreach ($cart as $item) {
    $precio = floatval($item['precio'] ?? $item['price'] ?? 0);
    $cantidad = intval($item['cantidad'] ?? 1);
    $subtotal += $precio * $cantidad;
}

$impuestos = $subtotal * 0.19; // 19% de IVA
$total = $subtotal + $impuestos + $envio;

try {
    // Iniciar transacción manual con mysqli
    mysqli_autocommit($conexion, FALSE);
    
    // Generar número de pedido único
    $fecha_actual = date('Ymd');
    $random = mt_rand(1000, 9999);
    $numero_pedido = "PED-{$fecha_actual}-{$random}";
    
    // Insertar pedido en la base de datos
    $sql = "INSERT INTO pedidos (usuario_id, numero_pedido, nombre, apellido, email, telefono, direccion, 
            departamento, ciudad, codigo_postal, tipo_envio, metodo_pago, notas, subtotal, impuestos, 
            envio, total, estado, fecha_pedido) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())";
    
    $stmt = mysqli_prepare($conexion, $sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
    }
    
    mysqli_stmt_bind_param($stmt, "isssssssssssdddds", 
        $usuario_id, $numero_pedido, $nombre, $apellido, $email, $telefono, $direccion, 
        $departamento, $ciudad, $codigo_postal, $tipo_envio, $metodo_pago, $notas, 
        $subtotal, $impuestos, $envio, $total
    );
    
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        throw new Exception("Error al insertar el pedido: " . mysqli_stmt_error($stmt));
    }
    
    // Obtener el ID del pedido insertado
    $pedido_id = mysqli_insert_id($conexion);
    
    // Crear tabla para detalles del pedido si no existe
    $sql = "CREATE TABLE IF NOT EXISTS pedido_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        producto_id INT NOT NULL,
        nombre_producto VARCHAR(255) NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        cantidad INT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        INDEX (pedido_id),
        INDEX (producto_id),
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conexion, $sql)) {
        throw new Exception("Error al crear la tabla pedido_detalles: " . mysqli_error($conexion));
    }
    
    // Insertar detalles del pedido
    foreach ($cart as $item) {
        $producto_id = $item['id'] ?? 0;
        $nombre_producto = $item['nombre'] ?? $item['name'] ?? "Producto ID: {$producto_id}";
        $precio = floatval($item['precio'] ?? $item['price'] ?? 0);
        $cantidad = intval($item['cantidad'] ?? 1);
        $subtotal_item = $precio * $cantidad;
        
        $sql = "INSERT INTO pedido_detalles (pedido_id, producto_id, nombre_producto, precio, cantidad, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexion, $sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de detalles: " . mysqli_error($conexion));
        }
        
        mysqli_stmt_bind_param($stmt, "iisddd", $pedido_id, $producto_id, $nombre_producto, $precio, $cantidad, $subtotal_item);
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception("Error al insertar detalle del pedido: " . mysqli_stmt_error($stmt));
        }
    }
    
    // Guardar dirección del usuario si se solicitó
    if (isset($_POST['guardar_direccion']) && $_POST['guardar_direccion'] == 1) {
        $sql = "UPDATE usuarios SET 
                direccion = ?, 
                ciudad = ?, 
                departamento = ?, 
                codigo_postal = ?, 
                telefono = ? 
                WHERE id = ?";
        
        $stmt = mysqli_prepare($conexion, $sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de actualización de usuario: " . mysqli_error($conexion));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssi", $direccion, $ciudad, $departamento, $codigo_postal, $telefono, $usuario_id);
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception("Error al actualizar datos del usuario: " . mysqli_stmt_error($stmt));
        }
    }
    
    // Confirmar transacción
    mysqli_commit($conexion);
    
    // Guardar información del pedido en la sesión para mostrarla en la página de confirmación
    $_SESSION['ultimo_pedido'] = $pedido_id;
    $_SESSION['numero_pedido'] = $numero_pedido;
    
    // Guardar datos adicionales para la página de confirmación
    $_SESSION['datos_pedido'] = [
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'departamento' => $departamento,
        'ciudad' => $ciudad,
        'codigo_postal' => $codigo_postal,
        'tipo_envio' => $tipo_envio,
        'metodo_pago' => $metodo_pago,
        'subtotal' => $subtotal,
        'impuestos' => $impuestos,
        'envio' => $envio,
        'total' => $total,
        'productos' => $cart
    ];
    
    // Limpiar carrito
    unset($_SESSION['carrito']);
    
    // Redirigir a la página de confirmación
    header('Location: confirmacion_pedido.php');
    exit;
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conexion);
    
    // Guardar mensaje de error
    $_SESSION['error'] = "Error al procesar el pedido: " . $e->getMessage();
    
    // Redirigir de vuelta al checkout
    header('Location: finalizar_compra.php');
    exit;
}
?>