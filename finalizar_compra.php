<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include('db.php');

if (!isset($_POST['cart'])) {
    header("Location: carrito.php?mensaje=Carrito vacío");
    exit();
}

$cart = json_decode($_POST['cart'], true);
if (empty($cart)) {
    header("Location: carrito.php?mensaje=Carrito vacío");
    exit();
}

$total = 0;
foreach ($cart as $item) {
    $total += floatval($item['price']) * (isset($item['cantidad']) ? $item['cantidad'] : 1);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Sabor Colombiano</title>
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
        h2, h4 {
            color: var(--color-primary);
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: var(--color-secondary);
        }
        .btn-submit {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }
        .btn-submit:hover {
            background-color: var(--color-primary);
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
            <a href="index.php"><img src="imagenes/logo.jpeg" alt="San Basilio de Palenque"></a>
        </div>
        <div>
            <span class="user-welcome"><i class="fas fa-user"></i> Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            <a href="carrito.php" class="btn-auth"><i class="fas fa-shopping-cart"></i> Carrito</a>
            <a href="index.php" class="btn-auth"><i class="fas fa-home"></i> Inicio</a>
            <a href="logout.php" class="btn-auth"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <div class="container">
        <h2>Finalizar Compra</h2>
        <form action="procesar_pedido.php" method="post">
            <h4>Resumen de tu Pedido</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                        <?php $quantity = isset($item['cantidad']) ? $item['cantidad'] : 1; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name'] ?? "Producto ID: {$item['id']}"); ?></td>
                            <td><?php echo $quantity; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['price'] * $quantity, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-3">
                <label class="form-label">Forma de Pago</label>
                <select name="forma_pago" class="form-select" required>
                    <option value="">Selecciona una opción</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                    <option value="transferencia">Transferencia Bancaria</option>
                </select>
            </div>

            <h4>Datos de Entrega</h4>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" required placeholder="Ej: Calle 123 #45-67">
            </div>
            <div class="mb-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="ciudad" name="ciudad" required placeholder="Ej: Bogotá">
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" required placeholder="Ej: 3001234567">
            </div>

            <input type="hidden" name="cart" value="<?php echo htmlspecialchars(json_encode($cart)); ?>">

            <div class="d-flex justify-content-between">
                <a href="carrito.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver al Carrito</a>
                <button type="submit" class="btn btn-submit"><i class="fas fa-check"></i> Confirmar Pedido</button>
            </div>
        </form>
    </div>

    <footer>
        <p>© 2025 Sabor Colombiano - Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>