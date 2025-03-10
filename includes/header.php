<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'El Palenque'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">El Palenque</h1>
                <nav>
                    <ul class="nav">
                        <li class="nav-item"><a href="index.php" class="nav-link text-white">Inicio</a></li>
                        <li class="nav-item"><a href="productos_compra.php" class="nav-link text-white">Productos</a></li>
                        <li class="nav-item"><a href="carrito.php" class="nav-link text-white">Carrito</a></li>
                        <li class="nav-item"><a href="mis_pedidos.php" class="nav-link text-white">Mis Pedidos</a></li>
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <li class="nav-item"><a href="logout.php" class="nav-link text-white">Cerrar Sesión</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a href="login.php" class="nav-link text-white">Iniciar Sesión</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>