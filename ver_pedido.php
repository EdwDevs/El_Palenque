<?php
// ver_pedido.php - Permite a los usuarios ver los detalles de un pedido específico
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
    $stmt = $conexion->prepare("SELECT COUNT(*) as count FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $pedido_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        header("Location: mis_pedidos.php?mensaje=No tienes permiso para ver este pedido&status=error");
        exit();
    }
    $stmt->close();
}

// Obtener datos del pedido
$stmt = $conexion->prepare("
    SELECT p.*, e.forma_pago, e.direccion, e.ciudad, e.telefono 
    FROM pedidos p 
    LEFT JOIN entregas e ON p.id = e.pedido_id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $numero_pedido = $row['numero_pedido'] ?? "PED-" . $pedido_id;
    $fecha = $row['fecha_pedido'];
    $total = $row['total'];
    $estado = $row['estado'];
    $forma_pago = $row['forma_pago'];
    $direccion = $row['direccion'];
    $ciudad = $row['ciudad'];
    $telefono = $row['telefono'];
    $subtotal = $row['subtotal'] ?? 0;
    $impuestos = $row['impuestos'] ?? 0;
    $envio = $row['envio'] ?? 0;
    $notas = $row['notas'] ?? '';
    
    // Si no hay subtotal, impuestos o envío en la tabla pedidos, los calculamos
    if ($subtotal == 0 && $impuestos == 0 && $envio == 0) {
        $envio = 12000; // Valor fijo de envío
        $subtotal = $total - $envio - ($total * 0.19 / 1.19);
        $impuestos = $total - $subtotal - $envio;
    }
} else {
    header("Location: mis_pedidos.php?mensaje=Pedido no encontrado&status=error");
    exit();
}
$stmt->close();

// Obtener los productos del pedido
try {
    // Primero verificamos si la tabla productos tiene una columna imagen
    $check_column = $conexion->query("SHOW COLUMNS FROM productos LIKE 'imagen'");
    $has_imagen = $check_column->num_rows > 0;
    
    if ($has_imagen) {
        // Si existe la columna imagen, usamos la consulta original
        $stmt = $conexion->prepare("
            SELECT dp.*, p.nombre, p.imagen 
            FROM detalles_pedido dp 
            LEFT JOIN productos p ON dp.producto_id = p.id 
            WHERE dp.pedido_id = ?
        ");
    } else {
        // Si no existe la columna imagen, la excluimos de la consulta
        $stmt = $conexion->prepare("
            SELECT dp.*, p.nombre 
            FROM detalles_pedido dp 
            LEFT JOIN productos p ON dp.producto_id = p.id 
            WHERE dp.pedido_id = ?
        ");
    }
    
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    // Si hay algún error con la consulta, usamos una versión más simple
    $stmt = $conexion->prepare("
        SELECT * FROM detalles_pedido WHERE pedido_id = ?
    ");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

$productos = [];
while ($row = $result->fetch_assoc()) {
    $precio = $row['precio_unitario'];
    $cantidad = $row['cantidad'];
    $subtotalItem = $precio * $cantidad;
    
    $productos[] = [
        'id' => $row['producto_id'],
        'nombre' => $row['nombre'] ?? 'Producto #' . $row['producto_id'],
        'precio' => $precio,
        'cantidad' => $cantidad,
        'subtotal' => $subtotalItem,
        'imagen' => $row['imagen'] ?? 'imagenes/producto_default.jpg'
    ];
}
$stmt->close();

// Formatear método de pago para mostrar
$metodo_pago_texto = '';
switch ($forma_pago) {
    case 'tarjeta':
        $metodo_pago_texto = 'Tarjeta de Crédito/Débito';
        break;
    case 'transferencia':
        $metodo_pago_texto = 'Transferencia Bancaria';
        break;
    case 'efectivo':
        $metodo_pago_texto = 'Efectivo (contra entrega)';
        break;
    case 'nequi':
        $metodo_pago_texto = 'Nequi / Daviplata';
        break;
    default:
        $metodo_pago_texto = $forma_pago;
}

// Formatear estado del pedido
$estado_texto = '';
$estado_clase = '';
switch ($estado) {
    case 'pendiente':
        $estado_texto = 'Pendiente';
        $estado_clase = 'warning';
        break;
    case 'procesando':
        $estado_texto = 'Procesando';
        $estado_clase = 'info';
        break;
    case 'enviado':
        $estado_texto = 'Enviado';
        $estado_clase = 'primary';
        break;
    case 'entregado':
        $estado_texto = 'Entregado';
        $estado_clase = 'success';
        break;
    case 'cancelado':
        $estado_texto = 'Cancelado';
        $estado_clase = 'danger';
        break;
    default:
        $estado_texto = ucfirst($estado);
        $estado_clase = 'secondary';
}

// Determinar tipo de envío (esto debería venir de la base de datos en un sistema real)
$tipo_envio = 'estandar'; // Por defecto
$tipo_envio_texto = $tipo_envio === 'express' ? 'Express (1-2 días)' : 'Estándar (3-5 días)';

// Calcular fecha estimada de entrega
$dias_entrega = $tipo_envio === 'express' ? 2 : 5;
$fecha_entrega = date('Y-m-d', strtotime($fecha . ' + ' . $dias_entrega . ' days'));
$fecha_entrega_formateada = date('d/m/Y', strtotime($fecha_entrega));

// Formatear fechas
$fecha_pedido_formateada = date('d/m/Y H:i', strtotime($fecha));

// Función para formatear precios
function formatear_precio($precio) {
    return number_format($precio, 0, ',', '.');
}

// Título de la página
$titulo = "Detalles del Pedido #" . $numero_pedido;

// Intentar incluir el encabezado con diferentes rutas
$header_paths = ['header.php', 'includes/header.php', '../header.php', '../includes/header.php'];
$header_included = false;

foreach ($header_paths as $path) {
    if (file_exists($path)) {
        include($path);
        $header_included = true;
        break;
    }
}

// Si no se encuentra el encabezado, crear uno básico
if (!$header_included) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $titulo . '</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    </head>
    <body>';
}
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="mis_pedidos.php">Mis Pedidos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pedido #<?php echo htmlspecialchars($numero_pedido); ?></li>
                </ol>
            </nav>
            <h1 class="mb-4">Detalles del Pedido #<?php echo htmlspecialchars($numero_pedido); ?></h1>
            
            <div class="alert alert-<?php echo $estado_clase; ?> d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>
                    Estado del pedido: <strong><?php echo htmlspecialchars($estado_texto); ?></strong>
                    <?php if ($estado == 'enviado'): ?>
                        <p class="mb-0 mt-1">Fecha estimada de entrega: <strong><?php echo $fecha_entrega_formateada; ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Productos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-3">Producto</th>
                                    <th scope="col" class="text-center">Precio</th>
                                    <th scope="col" class="text-center">Cantidad</th>
                                    <th scope="col" class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                                <small class="text-muted">ID: <?php echo $producto['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">$<?php echo formatear_precio($producto['precio']); ?></td>
                                    <td class="text-center align-middle"><?php echo $producto['cantidad']; ?></td>
                                    <td class="text-end pe-3 align-middle">$<?php echo formatear_precio($producto['subtotal']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-end">Subtotal:</td>
                                    <td class="text-end" width="120">$<?php echo formatear_precio($subtotal); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-end">IVA (19%):</td>
                                    <td class="text-end">$<?php echo formatear_precio($impuestos); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-end">Envío (<?php echo $tipo_envio_texto; ?>):</td>
                                    <td class="text-end">$<?php echo formatear_precio($envio); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>$<?php echo formatear_precio($total); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($notas)): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Notas del pedido</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($notas)); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Información del pedido</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Número de pedido:</span>
                            <span class="text-muted"><?php echo htmlspecialchars($numero_pedido); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Fecha de pedido:</span>
                            <span class="text-muted"><?php echo $fecha_pedido_formateada; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Estado:</span>
                            <span><span class="badge bg-<?php echo $estado_clase; ?>"><?php echo htmlspecialchars($estado_texto); ?></span></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Método de pago:</span>
                            <span class="text-muted"><?php echo htmlspecialchars($metodo_pago_texto); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Tipo de envío:</span>
                            <span class="text-muted"><?php echo htmlspecialchars($tipo_envio_texto); ?></span>
                        </li>
                        <?php if ($estado == 'enviado' || $estado == 'entregado'): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Fecha estimada de entrega:</span>
                            <span class="text-muted"><?php echo $fecha_entrega_formateada; ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Dirección de envío</h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong><br>
                        <?php echo htmlspecialchars($direccion); ?><br>
                        <?php echo htmlspecialchars($ciudad); ?><br>
                        Teléfono: <?php echo htmlspecialchars($telefono); ?>
                    </address>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="mis_pedidos.php" class="btn btn-outline-primary">Volver a mis pedidos</a>
                <?php if ($estado == 'pendiente'): ?>
                <a href="cancelar_pedido.php?id=<?php echo $pedido_id; ?>" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de que deseas cancelar este pedido?');">Cancelar pedido</a>
                <?php endif; ?>
                <a href="#" class="btn btn-outline-secondary" onclick="window.print();">
                    <i class="bi bi-printer"></i> Imprimir pedido
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    header, footer, .breadcrumb, .btn, nav {
        display: none !important;
    }
    .container {
        width: 100% !important;
        max-width: 100% !important;
    }
    .card {
        border: 1px solid #ddd !important;
    }
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    .badge {
        border: 1px solid #000 !important;
        color: #000 !important;
        background-color: transparent !important;
    }
}
</style>

<?php
// Intentar incluir el pie de página con diferentes rutas
$footer_paths = ['footer.php', 'includes/footer.php', '../footer.php', '../includes/footer.php'];
$footer_included = false;

foreach ($footer_paths as $path) {
    if (file_exists($path)) {
        include($path);
        $footer_included = true;
        break;
    }
}

// Si no se encuentra el pie de página, crear uno básico
if (!$footer_included) {
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
}
?>