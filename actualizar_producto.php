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

// Obtener el ID del producto desde la solicitud
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

// Consultar el producto específico
$stmt = $conexion->prepare("SELECT p.*, c.nombre_categoria 
                            FROM productos p 
                            LEFT JOIN categorias c ON p.categoria_id = c.categoria_id 
                            WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($fila = $result->fetch_assoc()) {
    // Datos del producto disponibles en $fila
} else {
    header("Location: productos.php?error=Producto no encontrado");
    exit();
}
$stmt->close();

// Consultar todas las categorías para el select
$category_query = "SELECT * FROM categorias";
$category_result = $conexion->query($category_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Formulario para modificar productos en San Basilio del Palenque">
    <title>Modificar Producto - San Basilio del Palenque</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Montserrat -->
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
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .contenedor {
            background-color: var(--color-light);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h3 {
            color: var(--color-primary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            font-weight: 700;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        label {
            font-weight: 600;
            color: var(--color-text);
        }

        input[type="text"],
        input[type="datetime-local"],
        select {
            padding: 0.75rem;
            border: 2px solid var(--color-accent);
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            transition: var(--transition);
        }

        input[type="text"]:focus,
        input[type="datetime-local"]:focus,
        select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
            outline: none;
        }

        input[type="submit"] {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 0.9rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        input[type="submit"]:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
        }

        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: var(--color-light);
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .success { background-color: var(--color-secondary); }
        .error { background-color: #f44336; }

        @media (max-width: 768px) {
            header { flex-direction: column; gap: 1rem; padding: 1rem; }
            .main-content { padding: 1rem; }
            .contenedor { padding: 1.5rem; }
            h3 { font-size: 1.5rem; }
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
        <span class="user-welcome"><i class="fas fa-user"></i> ¡Hola, <?php echo $username; ?>!</span>
        <a href="productos.php" class="btn-home"><i class="fas fa-arrow-left"></i> Regresar a Productos</a>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="contenedor">
            <h3>Modificar Producto</h3>
            <form id="updateForm" action="query_update_producto.php" method="POST" onsubmit="return handleSubmit(event)">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($fila['id']); ?>">
                
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($fila['nombre']); ?>" required>
                
                <label for="descripcion">Descripción</label>
                <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($fila['descripcion']); ?>" required>
                
                <label for="precio">Precio</label>
                <input type="text" id="precio" name="precio" value="<?php echo htmlspecialchars($fila['precio']); ?>" 
                       pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número válido (ej. 1234.56)" required>
                
                <label for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Seleccione una categoría</option>
                    <?php
                    $category_result->data_seek(0);
                    while ($cat = $category_result->fetch_assoc()) {
                        $selected = ($cat['categoria_id'] == $fila['categoria_id']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($cat['categoria_id']) . '" ' . $selected . '>' . htmlspecialchars($cat['nombre_categoria']) . '</option>';
                    }
                    ?>
                </select>
                
                <label for="fecha">Fecha de Creación</label>
                <input type="datetime-local" id="fecha" name="fecha_creacion" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($fila['fecha_creacion'])); ?>" required>
                
                <input type="submit" value="MODIFICAR">
            </form>
        </div>

        <!-- Notificación -->
        <div id="notification" class="notification"></div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
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
                    showNotification('Producto actualizado con éxito', 'success');
                    setTimeout(() => window.location.href = 'productos.php', 1500);
                } else {
                    showNotification('Error al actualizar el producto', 'error');
                }
            })
            .catch(() => showNotification('Error de conexión', 'error'));

            return false;
        }

        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }

        // Validación en tiempo real para el campo de precio
        document 🙂

.getElementById('precio').addEventListener('input', function() {
            const value = this.value;
            const regex = /^[0-9]+(\.[0-9]{0,2})?$/;
            if (!regex.test(value)) {
                this.style.borderColor = '#f44336';
            } else {
                this.style.borderColor = 'var(--color-accent)';
            }
        });

        // Animación al enfocar los campos
        const inputs = document.querySelectorAll('input[type="text"], input[type="datetime-local"], select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', () => {
                input.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>