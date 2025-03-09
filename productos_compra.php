<?php
// Iniciar sesión para gestionar datos del usuario
session_start();

// Verificar si el usuario está autenticado
$isLoggedIn = isset($_SESSION['usuario']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['usuario']) : 'Invitado';

// Incluir el archivo de conexión a la base de datos
include('db.php');

// Consultar productos con sus categorías usando un LEFT JOIN
$query = "SELECT p.*, c.nombre_categoria AS categoria 
          FROM productos p 
          LEFT JOIN categorias c ON p.categoria_id = c.categoria_id 
          ORDER BY c.nombre_categoria, p.nombre";
$result = $conexion->query($query);

// Verificar si la consulta falló
if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}

// Consultar todas las categorías para el filtro y navegación
$category_query = "SELECT * FROM categorias";
$category_result = $conexion->query($category_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Compra artesanías, plantas medicinales e instrumentos musicales de San Basilio del Palenque">
    <title>Productos - San Basilio del Palenque</title>

    <!-- Bootstrap CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Montserrat para una tipografía elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Estilos personalizados -->
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
            margin: 0;
            padding: 0;
            position: relative;
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
            flex-wrap: wrap;
        }

        .header-logo img {
            max-width: 120px;
            border-radius: 10px;
            border: 3px solid var(--color-primary);
            transition: var(--transition);
        }

        .header-logo img:hover {
            transform: scale(1.05);
        }

        .nav-links ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: var(--color-secondary);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--color-primary);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-welcome {
            color: var(--color-primary);
            font-weight: bold;
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
            transform: translateY(-2px);
        }

        .main-content {
            margin-top: 8rem;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-filter {
            background: var(--color-light);
            padding: 1rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid var(--color-accent);
            border-radius: 5px;
            font-size: 1rem;
        }

        .search-input:focus {
            border-color: var(--color-primary);
            outline: none;
            box-shadow: 0 0 5px rgba(255, 87, 34, 0.3);
        }

        .filter-select {
            padding: 0.75rem;
            border-radius: 5px;
            border: 2px solid var(--color-accent);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--color-light);
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .product-card.hidden {
            display: none; /* Ocultar productos no filtrados */
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--color-primary);
            margin: 1rem 0 0.5rem;
        }

        .product-category {
            font-size: 0.9rem;
            color: var(--color-secondary);
            margin-bottom: 0.5rem;
        }

        .product-description {
            font-size: 0.9rem;
            color: var(--color-text);
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--color-secondary);
        }

        .btn-add-cart {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-add-cart:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
        }

        footer {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.9);
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid var(--color-accent);
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links ul {
                flex-direction: column;
                text-align: center;
            }

            .main-content {
                padding: 1rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header con navegación y usuario -->
    <header>
        <div class="header-logo">
            <a href="index.php" title="Volver al inicio">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>

        <nav class="nav-links">
            <ul>
                <?php
                // Mostrar categorías dinámicamente en la navegación
                if ($category_result->num_rows > 0) {
                    while ($cat = $category_result->fetch_assoc()) {
                        $cat_id = htmlspecialchars($cat['categoria_id']);
                        $cat_name = htmlspecialchars($cat['nombre_categoria']);
                        echo '<li><a href="#' . strtolower(str_replace(' ', '-', $cat_name)) . '">' . $cat_name . '</a></li>';
                    }
                }
                ?>
            </ul>
        </nav>

        <div class="user-info">
            <span class="user-welcome">Hola, <?php echo $username; ?></span>
            <?php if ($isLoggedIn): ?>
                <a href="carrito.php" class="btn-auth"><i class="fas fa-shopping-cart"></i> Carrito</a>
                <a href="logout.php" class="btn-auth"><i class="fas fa-sign-out-alt"></i> Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn-auth"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <h1 class="text-center mb-4" style="color: var(--color-primary);">Productos de San Basilio del Palenque</h1>

        <!-- Barra de búsqueda y filtros -->
        <div class="search-filter">
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar productos...">
            <select id="categoryFilter" class="filter-select">
                <option value="">Todas las categorías</option>
                <?php
                // Rellenar el filtro de categorías dinámicamente
                $category_result->data_seek(0); // Reiniciar el puntero del resultado
                while ($cat = $category_result->fetch_assoc()) {
                    $cat_id = htmlspecialchars($cat['categoria_id']);
                    $cat_name = htmlspecialchars($cat['nombre_categoria']);
                    echo '<option value="' . $cat_id . '">' . $cat_name . '</option>';
                }
                ?>
            </select>
            <select id="sortFilter" class="filter-select">
                <option value="">Ordenar por</option>
                <option value="price-asc">Precio: Menor a Mayor</option>
                <option value="price-desc">Precio: Mayor a Menor</option>
            </select>
        </div>

        <!-- Grid de productos -->
        <div class="products-grid" id="productsGrid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $categoria_id = htmlspecialchars($row['categoria_id'] ?? '');
                    $categoria = htmlspecialchars($row['categoria'] ?? 'Sin categoría');
                    $nombre = htmlspecialchars($row['nombre']);
                    $descripcion = htmlspecialchars($row['descripcion'] ?? 'Sin descripción');
                    $precio = htmlspecialchars($row['precio']);
                    $imagen = htmlspecialchars($row['imagen'] ?? 'https://via.placeholder.com/300x200');
            ?>
                <div class="product-card" data-category="<?php echo $categoria_id; ?>" data-price="<?php echo $precio; ?>">
                    <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>" class="product-image">
                    <h3 class="product-title"><?php echo $nombre; ?></h3>
                    <p class="product-category"><?php echo $categoria; ?></p>
                    <p class="product-description"><?php echo $descripcion; ?></p>
                    <p class="product-price">$<?php echo number_format($precio, 2); ?></p>
                    <button class="btn-add-cart" onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo $nombre; ?>', <?php echo $precio; ?>)">
                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                    </button>
                </div>
            <?php
                }
            } else {
                echo '<p class="text-center">No hay productos disponibles en este momento.</p>';
            }
            ?>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>© 2025 San Basilio del Palenque - Hecho con orgullo en Colombia.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
        // Filtrar y ordenar productos en tiempo real
        function filterProducts() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;
            const products = document.querySelectorAll('.product-card');

            // Mostrar u ocultar productos en lugar de eliminarlos
            products.forEach(product => {
                const name = product.querySelector('.product-title').textContent.toLowerCase();
                const category = product.getAttribute('data-category');
                const matchesSearch = name.includes(searchValue);
                const matchesCategory = categoryFilter === "" || category === categoryFilter;

                if (matchesSearch && matchesCategory) {
                    product.classList.remove('hidden');
                } else {
                    product.classList.add('hidden');
                }
            });

            // Ordenar productos visibles
            if (sortFilter) {
                const visibleProducts = Array.from(products).filter(p => !p.classList.contains('hidden'));
                visibleProducts.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    if (sortFilter === 'price-asc') return priceA - priceB;
                    if (sortFilter === 'price-desc') return priceB - priceA;
                });

                const grid = document.getElementById('productsGrid');
                grid.innerHTML = ''; // Limpiar y reinsertar solo los visibles en orden
                visibleProducts.forEach(product => grid.appendChild(product));
            }
        }

        // Escuchar eventos de filtro
        document.getElementById('searchInput').addEventListener('input', filterProducts);
        document.getElementById('categoryFilter').addEventListener('change', filterProducts);
        document.getElementById('sortFilter').addEventListener('change', filterProducts);

        // Función para agregar al carrito (simulada)
        function addToCart(id, name, price, imagen) {
        alert(`Producto "${name}" ($ ${price}) agregado al carrito.`);
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.push({ id, name, price, imagen });
        localStorage.setItem('cart', JSON.stringify(cart));
    }

        // Navegación suave para enlaces de categorías
        document.querySelectorAll('.nav-links a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        // Ejecutar el filtro al cargar la página para mostrar todos los productos inicialmente
        window.addEventListener('load', filterProducts);
    </script>
</body>
</html>