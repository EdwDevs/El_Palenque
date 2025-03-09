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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - San Basilio del Palenque</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: #FF5722;
            --color-secondary: #4CAF50;
            --color-accent: #FFC107;
            --color-text: #333333;
            --color-light: #FFFFFF;
            --color-bg-light: #F9F9F9;
            --color-border: #E0E0E0;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --border-radius: 10px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            color: var(--color-text);
            margin: 0;
            padding: 0;
            padding-bottom: 2rem;
        }

        /* Header modernizado */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
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
            border-radius: 50%;
            border: 3px solid var(--color-primary);
            transition: var(--transition);
            object-fit: cover;
        }

        .header-logo img:hover {
            transform: scale(1.05);
        }

        .user-welcome {
            color: var(--color-primary);
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-welcome i {
            color: var(--color-secondary);
        }

        .btn-home {
            background-color: var(--color-primary);
            color: var(--color-light);
            padding: 0.7rem 1.2rem;
            border-radius: 30px;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-home:hover {
            background-color: var(--color-secondary);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Contenido principal */
        .main-content {
            margin-top: 8rem;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--color-light);
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-agregar {
            background-color: var(--color-primary);
            color: var(--color-light);
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-agregar:hover {
            background-color: var(--color-secondary);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Modal modernizado */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }

        .modal-content {
            background-color: var(--color-light);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            color: var(--color-primary);
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--color-text);
            cursor: pointer;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-modal:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--color-primary);
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--color-text);
            font-family: 'Montserrat', sans-serif;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--color-border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
            outline: none;
        }

        .form-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--color-border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }

        .form-select:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
            outline: none;
        }

        .btn-submit {
            width: 100%;
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Búsqueda modernizada */
        .search-container {
            margin: 0 auto 2rem;
            width: 100%;
            max-width: 600px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.5rem 1rem 3rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            font-family: 'Poppins', sans-serif;
        }

        .search-input:focus {
            border-color: var(--color-accent);
            background-color: var(--color-light);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-primary);
            font-size: 1.2rem;
        }

        /* Tabla modernizada */
        .table-container {
            background-color: var(--color-light);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .products-table th {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
        }

        .products-table th:first-child {
            border-top-left-radius: 10px;
        }

        .products-table th:last-child {
            border-top-right-radius: 10px;
        }

        .products-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--color-border);
            vertical-align: middle;
        }

        .products-table tr:last-child td {
            border-bottom: none;
        }

        .products-table tr {
            transition: var(--transition);
        }

        .products-table tr:hover {
            background-color: rgba(255, 193, 7, 0.05);
        }

        .price-cell {
            font-weight: 600;
            color: var(--color-primary);
        }

        .category-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--color-secondary);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .date-cell {
            font-size: 0.9rem;
            color: #666;
        }

        .action-cell {
            text-align: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-update {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--color-accent);
        }

        .btn-update:hover {
            background-color: var(--color-accent);
            color: var(--color-text);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-delete {
            background-color: rgba(255, 87, 34, 0.1);
            color: var(--color-primary);
        }

        .btn-delete:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Notificaciones */
        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: var(--color-light);
            z-index: 1000;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease;
            max-width: 300px;
        }

        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .success { 
            background-color: var(--color-secondary);
            border-left: 5px solid #2E7D32;
        }
        
        .error { 
            background-color: #f44336;
            border-left: 5px solid #B71C1C;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                padding: 1.5rem;
            }
            
            .content-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .main-content {
                margin-top: 12rem;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .products-table {
                min-width: 800px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }
            
            .modal-content {
                padding: 1.5rem;
            }
            
            .search-input {
                padding: 0.8rem 1rem 0.8rem 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header>
        <div class="header-logo">
            <a href="index.php" title="Volver al inicio">
                <img src="palenque.jpeg" alt="San Basilio de Palenque">
            </a>
        </div>
        <span class="user-welcome">
            <i class="fas fa-user"></i> ¡Hola, <?php echo $username; ?>!
        </span>
        <a href="index.php" class="btn-home">
            <i class="fas fa-arrow-left"></i> Regresar a Inicio
        </a>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="content-header">
            <h1 class="page-title">Gestión de Productos</h1>
            <button class="btn-agregar" onclick="openModal()">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
        </div>

        <!-- Campo de búsqueda -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Buscar productos por nombre, descripción o categoría..." onkeyup="searchProducts()">
        </div>

        <!-- Tabla de productos -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="products-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Fecha Creación</th>
                            <th>Editar</th>
                            <th>Borrar</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td class="price-cell">$<?php echo number_format($fila['precio'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($fila['categoria'] ?? 'Sin categoría'); ?>
                                </span>
                            </td>
                            <td class="date-cell"><?php echo date('d/m/Y H:i', strtotime($fila['fecha_creacion'])); ?></td>
                            <td class="action-cell">
                                <a href="actualizar_producto.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-update">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </td>
                            <td class="action-cell">
                                <a href="borrar_producto.php?id=<?php echo $fila['id']; ?>" 
                                   class="btn-action btn-delete"
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
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <i class="fas fa-box-open" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                No hay productos registrados en el sistema.
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para agregar productos -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Agregar Nuevo Producto</h3>
                    <button class="close-modal" onclick="closeModal()">×</button>
                </div>
                <form id="productForm" action="guardar_producto.php" method="POST" onsubmit="return handleSubmit(event)">
                    <div class="form-group">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Digite nombre del producto" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" placeholder="Digite descripción del producto" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Precio</label>
                        <input type="text" name="precio" class="form-control" placeholder="Ej: 25000" required pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número válido (ej. 25000 o 25000.50)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-select" required>
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
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Fecha de creación</label>
                        <input type="datetime-local" id="fecha" name="fecha_creacion" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> GUARDAR PRODUCTO
                    </button>
                </form>
            </div>
        </div>

        <!-- Notificación -->
        <div id="notification" class="notification"></div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
        // Establecer la fecha actual en el campo de fecha
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('fecha').value = currentDateTime;
        });

        // Abrir el modal
        function openModal() {
            document.getElementById('productModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Evitar scroll
        }

        // Cerrar el modal
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restaurar scroll
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
            return true;
        }

        // Mostrar notificaciones
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            
            // Añadir icono según el tipo
            const icon = document.createElement('i');
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            icon.style.marginRight = '8px';
            notification.prepend(icon);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                    notification.style.opacity = '1';
                }, 300);
            }, 3000);
        }

        // Buscar productos en tiempo real
        function searchProducts() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('productsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length === 0) continue; // Saltar filas sin celdas
                
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