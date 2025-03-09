<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include('db.php');
require('fpdf/fpdf.php'); // Incluir FPDF

// Obtener el ID del usuario desde la sesión
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
        die("Usuario no encontrado en la base de datos.");
    }
    $stmt->close();
}

// Verificar si se proporcionó un ID de pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_pedidos.php?mensaje=ID de pedido inválido");
    exit();
}

$pedido_id = intval($_GET['id']);
$isAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

// Obtener el pedido específico
if ($isAdmin) {
    $stmt = $conexion->prepare("
        SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $pedido_id);
} else {
    $stmt = $conexion->prepare("
        SELECT p.id, p.usuario_id, p.fecha_pedido, p.total, p.estado, u.nombre AS usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = ? AND p.usuario_id = ?
    ");
    $stmt->bind_param("ii", $pedido_id, $usuario_id);
}
$stmt->execute();
$result_pedido = $stmt->get_result();
$pedido = $result_pedido->fetch_assoc();
$stmt->close();

if (!$pedido) {
    header("Location: gestion_pedidos.php?mensaje=Pedido no encontrado o no tienes permiso");
    exit();
}

// Obtener detalles del pedido
$stmt = $conexion->prepare("
    SELECT dp.producto_id, dp.cantidad, dp.precio_unitario, p.nombre 
    FROM detalles_pedido dp 
    LEFT JOIN productos p ON dp.producto_id = p.id 
    WHERE dp.pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result_detalles = $stmt->get_result();
$detalles = [];
while ($row = $result_detalles->fetch_assoc()) {
    $detalles[] = $row;
}
$stmt->close();

// Obtener datos de entrega
$stmt = $conexion->prepare("
    SELECT forma_pago, direccion, ciudad, telefono 
    FROM entregas 
    WHERE pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result_entrega = $stmt->get_result();
$entrega = $result_entrega->fetch_assoc();
$stmt->close();

// Generar PDF y actualizar estado si se solicita facturar
if (isset($_GET['facturar']) && $_GET['facturar'] == '1') {
    // Actualizar el estado del pedido a "confirmado"
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'confirmado' WHERE id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $stmt->close();

    // Generar el PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Título
    $pdf->Cell(0, 10, 'Factura - Sabor Colombiano', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R');
    $pdf->Ln(10);

    // Información del pedido
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Detalles del Pedido #{$pedido['id']}", 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Cliente: {$pedido['usuario_nombre']}", 0, 1);
    $pdf->Cell(0, 10, "Fecha del Pedido: " . date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])), 0, 1);
    $pdf->Cell(0, 10, "Estado: Confirmado", 0, 1);
    $pdf->Ln(10);

    // Datos de entrega
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Datos de Entrega", 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Forma de Pago: " . ucfirst($entrega['forma_pago'] ?? 'No especificado'), 0, 1);
    $pdf->Cell(0, 10, "Dirección: " . ($entrega['direccion'] ?? 'No especificado'), 0, 1);
    $pdf->Cell(0, 10, "Ciudad: " . ($entrega['ciudad'] ?? 'No especificado'), 0, 1);
    $pdf->Cell(0, 10, "Teléfono: " . ($entrega['telefono'] ?? 'No especificado'), 0, 1);
    $pdf->Ln(10);

    // Tabla de productos
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, 'Producto', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(40, 10, 'Precio Unitario', 1);
    $pdf->Cell(40, 10, 'Subtotal', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($detalles as $detalle) {
        $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
        $pdf->Cell(80, 10, $detalle['nombre'] ?: "Producto ID: {$detalle['producto_id']}", 1);
        $pdf->Cell(30, 10, $detalle['cantidad'], 1, 0, 'C');
        $pdf->Cell(40, 10, '$' . number_format($detalle['precio_unitario'], 2), 1, 0, 'R');
        $pdf->Cell(40, 10, '$' . number_format($subtotal, 2), 1, 0, 'R');
        $pdf->Ln();
    }

    // Total
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, 'Total:', 0);
    $pdf->Cell(40, 10, '$' . number_format($pedido['total'], 2), 0, 1, 'R');

    // Descargar el PDF
    $pdf->Output('D', "factura_pedido_{$pedido_id}.pdf");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido #<?php echo $pedido_id; ?> - Sabor Colombiano</title>
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
        .user-welcome {
            color: var(--color-primary);
            font-weight: bold;
            margin-right: 1rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .pedido-card {
            padding: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
        }
        .pedido-card h3 {
            color: var(--color-primary);
            margin-bottom: 1rem;
        }
        .table {
            margin-top: 1rem;
        }
        footer {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.9);
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: var(--color-light);
            z-index: 1000;
            box-shadow: var(--shadow);
            display: none;
        }
        .success {
            background-color: var(--color-secondary);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-logo">
            <a href="index.php"><img src="palenque.jpeg" alt="San Basilio de Palenque"></a>
        </div>
        <div>
            <span class="user-welcome"><i class="fas fa-user"></i> Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            <a href="<?php echo $isAdmin ? 'gestion_pedidos.php' : 'ver_pedido.php'; ?>" class="btn-auth"><i class="fas fa-list"></i> Mis Pedidos</a>
            <a href="index.php" class="btn-auth"><i class="fas fa-home"></i> Inicio</a>
            <a href="logout.php" class="btn-auth"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <div class="container">
        <h2 class="text-center mb-4">Detalles del Pedido #<?php echo htmlspecialchars($pedido['id']); ?></h2>
        <div class="pedido-card">
            <h3>Pedido #<?php echo htmlspecialchars($pedido['id']); ?></h3>
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($pedido['usuario_nombre']); ?></p>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
            <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
            <p><strong>Estado:</strong> <?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?></p>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td>
                                <?php if ($detalle['nombre']): ?>
                                    <?php echo htmlspecialchars($detalle['nombre']); ?>
                                <?php else: ?>
                                    Producto ID: <?php echo $detalle['producto_id']; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                            <td>$<?php echo number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-between">
                <a href="<?php echo $isAdmin ? 'gestion_pedidos.php' : 'ver_pedido.php'; ?>" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver</a>
                <?php if ($pedido['estado'] !== 'confirmado'): ?>
                    <a href="ver_pedido.php?id=<?php echo $pedido_id; ?>&facturar=1" class="btn btn-success"><i class="fas fa-file-pdf"></i> Facturar</a>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled><i class="fas fa-file-pdf"></i> Ya Facturado</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notificación para mensajes -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div id="notification" class="notification success">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>

    <footer>
        <p>© 2025 Sabor Colombiano - Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar notificación y ocultarla después de 3 segundos
        <?php if (isset($_GET['mensaje'])): ?>
            document.getElementById('notification').style.display = 'block';
            setTimeout(() => {
                document.getElementById('notification').style.display = 'none';
            }, 3000);
        <?php endif; ?>

        // Limpiar localStorage si clear_cart=1 está en la URL
        <?php if (isset($_GET['clear_cart']) && $_GET['clear_cart'] == '1'): ?>
            localStorage.removeItem('cart');
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conexion->close(); ?>