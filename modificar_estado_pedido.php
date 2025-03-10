<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include('db.php');

// Verificar si se proporcionó un ID de pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de pedido no válido.");
}
$pedido_id = intval($_GET['id']);

// Obtener datos del pedido
$stmt = $conexion->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    die("Pedido no encontrado.");
}

// Obtener detalles del pedido
$stmt = $conexion->prepare("SELECT dp.id, dp.producto_id, dp.cantidad, dp.precio_unitario, p.nombre 
                            FROM detalles_pedido dp 
                            LEFT JOIN productos p ON dp.producto_id = p.id 
                            WHERE dp.pedido_id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result_detalles = $stmt->get_result();
$detalles = $result_detalles->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Manejar la actualización del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];
    $total = floatval($_POST['total']);
    $detalles_form = $_POST['detalles']; // Array con los detalles

    // Iniciar transacción para asegurar consistencia
    $conexion->begin_transaction();

    try {
        // Actualizar el pedido principal
        $stmt = $conexion->prepare("UPDATE pedidos SET estado = ?, total = ? WHERE id = ?");
        $stmt->bind_param("sdi", $estado, $total, $pedido_id);
        $stmt->execute();
        $stmt->close();

        // Actualizar los detalles del pedido
        foreach ($detalles_form as $detalle_id => $data) {
            $producto_id = intval($data['producto_id']);
            $cantidad = intval($data['cantidad']);
            $precio_unitario = floatval($data['precio_unitario']);

            $stmt = $conexion->prepare("UPDATE detalles_pedido SET producto_id = ?, cantidad = ?, precio_unitario = ? WHERE id = ?");
            $stmt->bind_param("iidi", $producto_id, $cantidad, $precio_unitario, $detalle_id);
            $stmt->execute();
            $stmt->close();
        }

        // Confirmar transacción
        $conexion->commit();
        $success = "Pedido actualizado correctamente.";
    } catch (Exception $e) {
        $conexion->rollback();
        $error = "Error al actualizar el pedido: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Pedido - Sabor Colombiano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --color-primary: #FF5722;
        --color-secondary: #4CAF50;
        --color-accent: #FFC107;
        --color-text: #333333;
        --color-light: #FFFFFF;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
        min-height: 100vh;
        color: var(--color-text);
        padding-top: 100px;
        padding-bottom: 60px;
    }

    header {
        background: rgba(255, 255, 255, 0.95);
        padding: 1rem 2rem;
        box-shadow: var(--shadow);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-logo img {
        max-width: 120px;
        border-radius: 10px;
        border: 3px solid var(--color-primary);
    }

    .btn-auth {
        background-color: var(--color-primary);
        color: var(--color-light);
        padding: 0.5rem 1rem;
        border-radius: 5px;
        text-decoration: none;
        transition: var(--transition);
    }

    .btn-auth:hover {
        background-color: var(--color-secondary);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        box-shadow: var(--shadow);
    }

    .form-control-sm {
        max-width: 150px;
    }

    footer {
        text-align: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.9);
        position: fixed;
        bottom: 0;
        width: 100%;
    }
    </style>
</head>

<body>
    <header>
        <div class="header-logo">
            <a href="index.php"><img src="palenque.jpeg" alt="San Basilio de Palenque"></a>
        </div>
        <div>
            <span class="user-welcome"><i class="fas fa-user"></i> Hola,
                <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            <a href="ver_pedido.php" class="btn-auth"><i class="fas fa-list"></i> Ver Pedidos</a>
            <a href="logout.php" class="btn-auth"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <div class="container">
        <h2 class="text-center mb-4">Modificar Pedido #<?php echo $pedido_id; ?></h2>

        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="pendiente" <?php echo $pedido['estado'] === 'pendiente' ? 'selected' : ''; ?>>
                        Pendiente</option>
                    <option value="confirmado" <?php echo $pedido['estado'] === 'confirmado' ? 'selected' : ''; ?>>
                        Confirmado</option>
                    <option value="en_proceso" <?php echo $pedido['estado'] === 'en_proceso' ? 'selected' : ''; ?>>En
                        Proceso</option>
                    <option value="enviado" <?php echo $pedido['estado'] === 'enviado' ? 'selected' : ''; ?>>Enviado
                    </option>
                    <option value="entregado" <?php echo $pedido['estado'] === 'entregado' ? 'selected' : ''; ?>>
                        Entregado</option>
                    <option value="cancelado" <?php echo $pedido['estado'] === 'cancelado' ? 'selected' : ''; ?>>
                        Cancelado</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="total" class="form-label">Total</label>
                <input type="number" step="0.01" name="total" id="total" class="form-control"
                    value="<?php echo $pedido['total']; ?>" required>
            </div>

            <h4>Detalles del Pedido</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td>
                            <input type="number" name="detalles[<?php echo $detalle['id']; ?>][producto_id]"
                                class="form-control form-control-sm" value="<?php echo $detalle['producto_id']; ?>"
                                required>
                            <small><?php echo $detalle['nombre'] ? htmlspecialchars($detalle['nombre']) : 'ID: ' . $detalle['producto_id']; ?></small>
                        </td>
                        <td>
                            <input type="number" name="detalles[<?php echo $detalle['id']; ?>][cantidad]"
                                class="form-control form-control-sm" value="<?php echo $detalle['cantidad']; ?>" min="1"
                                required>
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                name="detalles[<?php echo $detalle['id']; ?>][precio_unitario]"
                                class="form-control form-control-sm" value="<?php echo $detalle['precio_unitario']; ?>"
                                required>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
            <a href="ver_pedido.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </form>
    </div>

    <footer>
        <p>© 2025 Sabor Colombiano - Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conexion->close(); ?>