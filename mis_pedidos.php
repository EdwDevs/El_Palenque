<?php
// mis_pedidos.php - Muestra la lista de pedidos del usuario
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

include('db.php');

$usuario_id = $_SESSION['usuario_id'];

// Obtener todos los pedidos del usuario
$stmt = $conexion->prepare("
    SELECT p.*, COUNT(dp.id) as total_productos 
    FROM pedidos p 
    LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id 
    WHERE p.usuario_id = ? 
    GROUP BY p.id 
    ORDER BY p.fecha_pedido DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$pedidos = [];
while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}
$stmt->close();

// Función para formatear precios
function formatear_precio($precio) {
    return number_format($precio, 0, ',', '.');
}

// Incluir el encabezado
$titulo = "Mis Pedidos";
include('includes/header.php');
?>

<div class="container my-5">
    <h1 class="mb-4">Mis Pedidos</h1>

    <?php if (isset($_GET['mensaje'])): ?>
    <div class="alert alert-<?php echo ($_GET['status'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
    <div class="alert alert-info">
        <p class="mb-0">No tienes pedidos realizados todavía.</p>
    </div>
    <div class="text-center mt-4">
        <a href="productos_compra.php" class="btn btn-primary">Ir a comprar</a>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Historial de pedidos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Pedido #</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Total</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                        <?php 
                                    // Formatear estado
                                    $estado_clase = '';
                                    switch ($pedido['estado']) {
                                        case 'pendiente': $estado_clase = 'warning'; break;
                                        case 'procesando': $estado_clase = 'info'; break;
                                        case 'enviado': $estado_clase = 'primary'; break;
                                        case 'entregado': $estado_clase = 'success'; break;
                                        case 'cancelado': $estado_clase = 'danger'; break;
                                        default: $estado_clase = 'secondary';
                                    }
                                ?>
                        <tr>
                            <td>
                                <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($pedido['numero_pedido'] ?? "PED-" . $pedido['id']); ?>
                                </a>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td><?php echo $pedido['total_productos']; ?> artículos</td>
                            <td>$<?php echo formatear_precio($pedido['total']); ?></td>
                            <td><span
                                    class="badge bg-<?php echo $estado_clase; ?>"><?php echo ucfirst($pedido['estado']); ?></span>
                            </td>
                            <td>
                                <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    Ver detalles
                                </a>
                                <?php if ($pedido['estado'] == 'pendiente'): ?>
                                <a href="cancelar_pedido.php?id=<?php echo $pedido['id']; ?>"
                                    class="btn btn-sm btn-outline-danger ms-1"
                                    onclick="return confirm('¿Estás seguro de que deseas cancelar este pedido?');">
                                    Cancelar
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>