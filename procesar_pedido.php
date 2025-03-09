<?php
// Iniciar sesión y verificar autenticación
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si hay datos del carrito
if (!isset($_POST['cart']) || empty($_POST['cart'])) {
    // Intentar obtener el carrito desde la sesión
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
    } else {
        // No hay carrito ni en POST ni en sesión
        header("Location: carrito.php?message=No hay productos en el carrito&status=error");
        exit();
    }
} else {
    // Decodificar el carrito desde POST
    $cart = json_decode($_POST['cart'], true);
    if (empty($cart)) {
        header("Location: carrito.php?message=El carrito está vacío&status=error");
        exit();
    }
}

// Obtener datos del formulario
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

// Calcular totales
$subtotal = floatval($_POST['subtotal'] ?? 0);
$impuestos = floatval($_POST['impuestos'] ?? 0);
$envio = floatval($_POST['envio'] ?? 15000);
if ($tipo_envio === 'express') {
    $envio += 10000; // Agregar costo de envío express
}
$total = floatval($_POST['total'] ?? ($subtotal + $impuestos + $envio));

// Generar número de pedido único
$pedido_id = 'PED-' . date('YmdHis') . '-' . substr(md5(uniqid(mt_rand(), true)), 0, 6);

// Guardar datos del pedido en la sesión para mostrarlos en la confirmación
$_SESSION['ultimo_pedido'] = [
    'pedido_id' => $pedido_id,
    'fecha' => date('Y-m-d H:i:s'),
    'cart' => $cart,
    'subtotal' => $subtotal,
    'impuestos' => $impuestos,
    'envio' => $envio,
    'total' => $total,
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
    'notas' => $notas,
    'estado' => 'pendiente'
];

// Intentar guardar el pedido en la base de datos (si está disponible)
$pedido_guardado = false;
try {
    // Incluir conexión a la base de datos
    if (file_exists('db.php')) {
        include('db.php');
        
        // Verificar si existe la conexión
        if (isset($conn) && $conn instanceof mysqli) {
            // Preparar datos para insertar en la tabla de pedidos
            $usuario_id = $_SESSION['usuario_id'] ?? 0;
            $fecha = date('Y-m-d H:i:s');
            $estado = 'pendiente';
            $productos_json = json_encode($cart);
            
            // Insertar pedido en la base de datos
            $query = "INSERT INTO pedidos (pedido_id, usuario_id, fecha, nombre, apellido, email, telefono, 
                      direccion, departamento, ciudad, codigo_postal, tipo_envio, metodo_pago, 
                      subtotal, impuestos, envio, total, productos, notas, estado) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("sisssssssssssdddssss", 
                    $pedido_id, $usuario_id, $fecha, $nombre, $apellido, $email, $telefono, 
                    $direccion, $departamento, $ciudad, $codigo_postal, $tipo_envio, $metodo_pago, 
                    $subtotal, $impuestos, $envio, $total, $productos_json, $notas, $estado);
                
                $pedido_guardado = $stmt->execute();
                $stmt->close();
            }
        }
    }
} catch (Exception $e) {
    // Registrar error pero continuar con el proceso
    error_log("Error al guardar pedido: " . $e->getMessage());
}

// Limpiar el carrito después de procesar el pedido
unset($_SESSION['cart']);
// También limpiar el carrito en localStorage mediante JavaScript

// Redirigir a la página de confirmación
header("Location: confirmacion_pedido.php?pedido=" . $pedido_id);
exit();
?>