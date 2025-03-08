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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Sabor Colombiano</title>
    
    <!-- Enlace al archivo de estilos externos -->
    <link rel="stylesheet" href="estilos_home.css">
    
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos inline para mantener consistencia y agregar interactividad -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #ffffff;
            text-align: center;
            padding: 10px 0;
            border-bottom: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        header h1 {
            margin: 0;
            display: inline-block;
        }

        header img {
            max-width: 1000px;
            height: 150px;
            vertical-align: middle;
            transition: transform 0.3s ease;
        }

        header img:hover {
            transform: scale(1.05);
        }

        .user-welcome {
            font-size: 1.1rem;
            color: #333;
            margin: 0 20px;
            font-weight: bold;
        }

        .btn-home {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 20px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-home:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .contenedor {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .btn-agregar {
            background-color: #FF5722;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-agregar:hover {
            background-color: #e64a19;
            transform: translateY(-2px);
        }

        /* Estilos del modal */
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
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
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
            color: #333;
            cursor: pointer;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .modal-content label {
            font-weight: bold;
        }

        .modal-content input[type="text"],
        .modal-content input[type="datetime-local"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        .modal-content input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Estilo del campo de búsqueda */
        .search-container {
            margin: 20px auto;
            width: 80%;
            max-width: 600px;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        /* Estilo de la tabla */
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        table tr {
            transition: background-color 0.3s ease;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table a {
            text-decoration: none;
            color: #FF5722;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }

        table a:hover {
            background-color: #ffebee;
            color: #e64a19;
        }

        /* Estilo para mensajes de notificación */
        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
        }

        .success {
            background-color: #4CAF50;
        }

        .error {
            background-color: #f44336;
        }

        @media (max-width: 768px) {
            header { flex-direction: column; gap: 10px; }
            header img { max-width: 100%; height: auto; }
            .contenedor, table, .search-container { width: 95%; }
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo, usuario logueado y botón de regresar a home -->
    <header>
        <a href="home.php">
            <h1><img src="imagenes/logo.jpeg" alt="PALENQUE" width="1000" height="150"></h1>
        </a>
        <span class="user-welcome">¡Hola, <?php echo $username; ?>!</span>
        <a href="index.php" class="btn-header"><i class="fas fa-arrow-left"></i> Regresar a Inicio</a>
    </header>

    <!-- Contenedor con botón para abrir el modal -->
    <div class="contenedor">
        <button class="btn-agregar" onclick="openModal()">Agregar Producto</button>
    </div>

    <!-- Modal para agregar productos -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3>Ingresar Producto</h3>
            <form id="productForm" action="guardar_producto.php" method="POST" onsubmit="return handleSubmit(event)">
                <label>Nombre</label>
                <input type="text" name="nombre" placeholder="Digite nombre" required>
                
                <label>Descripción</label>
                <input type="text" name="descripcion" placeholder="Digite descripción" required>
                
                <label>Precio</label>
                <input type="text" name="precio" placeholder="Precio" required pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número válido (ej. 123.45)">
                
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

    <!-- Tabla para mostrar los productos -->
    <table id="productsTable" border="1">
        <tr>
            <th>Nombres</th>
            <th>Precio</th>
            <th>Fecha creación</th>
            <th>Editar</th>
            <th>Borrar</th>
        </tr>
        <?php
        // Consultar todos los productos de la base de datos
        $sel = $conexion->query("SELECT * FROM productos ORDER BY id ASC");
        
        if ($sel->num_rows > 0) {
            while ($fila = $sel->fetch_assoc()) {
        ?>
        <tr>
            <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
            <td><?php echo htmlspecialchars($fila['precio']); ?></td>
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
            <td colspan="5">No hay productos registrados en el sistema.</td>
        </tr>
        <?php
        }
        ?>
    </table>

    <!-- Notificación para mensajes -->
    <div id="notification" class="notification"></div>

    <!-- Scripts para interactividad -->
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
                    setTimeout(() => location.reload(), 1500); // Recargar después de 1.5s
                } else {
                    showNotification('Error al guardar el producto', 'error');
                }
            })
            .catch(() => showNotification('Error de conexión', 'error'));

            return false;
        }

        // Confirmación personalizada para eliminar
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

        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeModal();
            }
        };
    </script>
</body>
</html>