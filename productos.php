<?php
// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Verificar si el usuario está autenticado; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Almacenar el nombre del usuario logueado con seguridad contra XSS
$username = htmlspecialchars($_SESSION['usuario']);

// Incluir el archivo de conexión a la base de datos
include('db.php');

// Consultar todas las categorías para el modal
$category_query = "SELECT * FROM categorias";
$category_result = $conexion->query($category_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .user-welcome {
            color: var(--color-primary);
            font-weight: bold;
            margin: 0 1rem;
        }

        .btn-home {
            background-color: var(--color-primary);
            color: var(--color-light);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-home:hover {
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

        .btn-agregar {
            background-color: var(--color-primary);
            color: var(--color-light);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }

        .btn-agregar:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }

        .modal-content {
            background-color: var(--color-light);
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow);
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: var(--color-primary);
            cursor: pointer;
        }

        .close-modal:hover {
            color: var(--color-secondary);
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .modal-content label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .modal-content input[type="text"],
        .modal-content input[type="datetime-local"],
        .modal-content select {
            padding: 8px;
            border: 2px solid var(--color-accent);
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .modal-content input[type="submit"] {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .modal-content input[type="submit"]:hover {
            background-color: var(--color-primary);
        }

        /* Búsqueda */
        .search-container {
            margin: 20px auto;
            width: 80%;
            max-width: 600px;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--color-accent);
            border-radius: 5px;
            font-size: 16px;
        }

        /* Tabla */
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: var(--color-light);
            box-shadow: var(--shadow);
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }

        table tr {
            transition: background-color 0.3s ease;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table a {
            text-decoration: none;
            color: var(--color-primary);
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
            transition: var(--transition);
        }

        table a:hover {
            background-color: #ffebee;
            color: #e64a19;
        }

        /* Notificaciones */
        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: var(--color-light);
            z-index: 1000;
        }

        .success { background-color: var(--color-secondary); }
        .error { background-color: #f44336; }

        @media (max-width: 768px) {
            header { flex-direction: column; gap: 1rem; }
            .main-content { padding: 1rem; }
            table, .search-container { width: 95%; }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header>
        <div class="header-logo">
            <a href="index.php" title="Volver al inicio">
                <img src="imagenes/logo.jpeg" alt="San Basilio de Palenque">
            </a>
        </div>
        <span class="user-welcome">¡Hola, <?php echo $username; ?>!</span>
        <a href="index.php" class="btn-home"><i class="fas fa-arrow-left"></i> Regresar a Inicio</a>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="contenedor text-center">
            <button class="btn-agregar" onclick="openModal()">Agregar Producto</button>
        </div>

        <!-- Modal para agregar productos -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">×</span>
                <h3>Ingresar Producto</h3>
                <form id="productForm" action="guardar_producto.php" method="POST" onsubmit="return handleSubmit(event)">
                    <label>Nombre</label>
                    <input type="text" name="nombre" placeholder="Digite nombre" required>
                    
                    <label>Descripción</label>
                    <input type="text" name="descripcion" placeholder="Digite descripción" required>
                    
                    <label>Precio</label>
                    <input type="text" name="precio" placeholder="Precio" required pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número válido (ej. 123.45)">
                    
                    <label>Categoría</label>
                    <select name="categoria_id" required>
                        <option value="">Seleccione una categoría</option>
                        <?php
                        $category_result->data_seek(0);
                        while ($cat = $category_result->fetch_assoc()) {
                            $cat_id = htmlspecialchars($cat['categoria_id']);
                            $cat_name = htmlspecialchars($cat['nombre_categoria']);
                            echo '<option value="' . $cat_id . '">' . $cat_name . '</option>';
                        }
                        ?>
                    </select>
                    
                    <label for="fecha">Fecha de creación</label>
                    <input type="datetime-local" id="fecha" name="fecha_creacion" required>
                    
                    <input type="submit" value="GUARDAR">
                </form>
            </div>
        </div>

        <!-- Campo de búsqueda -->
        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" placeholder="Buscar productos..." onkeyup="searchProducts()">
        </div>

        <!-- Tabla de productos -->
        <table id="productsTable" border="1">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Fecha Creación</th>
                <th>Editar</th>
                <th>Borrar</th>
            </tr>
            <?php
            // Consultar productos con sus categorías
            $sel = $conexion->query("SELECT p.*, c.nombre_categoria AS categoria 
                                    FROM productos p 
                                    LEFT JOIN categorias c ON p.categoria_id = c.categoria_id 
                                    ORDER BY p.id ASC");
            
            if ($sel->num_rows > 0) {
                while ($fila = $sel->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                <td><?php echo htmlspecialchars($fila['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($fila['precio']); ?></td>
                <td><?php echo htmlspecialchars($fila['categoria'] ?? 'Sin categoría'); ?></td>
                <td><?php echo htmlspecialchars($fila['fecha_creacion']); ?></td>
                <td>
                    <a href="actualizar_producto.php?id=<?php echo $fila['id']; ?>">Actualizar</a>
                </td>
                <td>
                    <a href="borrar_producto.php?id=<?php echo $fila['id']; ?>" 
                       onclick="return confirmDelete(event, '<?php echo $fila['nombre']; ?>')">
                       <i class="fas fa-trash"></i> Eliminar
                    </a>
                </td>
            </tr>
            <?php
                }
            } else {
            ?>
            <tr>
                <td colspan="7">No hay productos registrados en el sistema.</td>
            </tr>
            <?php
            }
            ?>
        </table>

        <!-- Notificación -->
        <div id="notification" class="notification"></div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
        // Abrir el modal
        function openModal() {
            document.getElementById('productModal').style.display = 'flex';
        }

        // Cerrar el modal
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Manejar el envío del formulario
        function handleSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            showNotification('Producto guardado con éxito', 'success');
            form.reset();
            closeModal();
            setTimeout(() => location.reload(), 1500); // Recargar para mostrar el nuevo producto
        } else {
            response.text().then(text => {
                showNotification('Error al guardar el producto: ' + text, 'error');
            });
        }
    })
    .catch(error => showNotification('Error de conexión: ' + error, 'error'));

    return false;
}

        // Confirmación para eliminar
        function confirmDelete(event, productName) {
            if (!confirm(`¿Estás seguro de que quieres eliminar "${productName}"?`)) {
                event.preventDefault();
                return false;
            }
            showNotification(`"${productName}" eliminado`, 'success');
            setTimeout(() => location.reload(), 1500);
            return true;
        }

        // Mostrar notificaciones
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }

        // Buscar productos en tiempo real
        function searchProducts() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('productsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length - 2; j++) { // Excluir columnas de acciones
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(searchValue)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeModal();
            }
        };
    </script>
</body>
</html>