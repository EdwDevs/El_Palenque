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

// Incluir el archivo de conexión a la base de datos (asegúrate de que db.php esté configurado)
include('db.php');

// Obtener el ID del producto desde la solicitud (usando REQUEST para compatibilidad)
$id = $_REQUEST['id'];

// Consultar el producto específico en la base de datos usando una consulta preparada para mayor seguridad
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id); // "i" indica que el parámetro es un entero
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el producto y obtener los datos
if ($fila = $result->fetch_assoc()) {
    // Datos del producto disponibles en $fila
} else {
    // Redirigir a productos.php si no se encuentra el producto
    header("Location: productos.php");
    exit();
}

// Cerrar la declaración preparada
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Formulario para modificar productos en Sabor Colombiano">
    <title>Modificar Producto - Sabor Colombiano</title>
    
    <!-- Enlace al archivo de estilos externo (si existe, mantenlo; si no, los estilos inline lo reemplazan) -->
    <link rel="stylesheet" type="text/css" href="estilo_actualizar.css">
    
    <!-- Font Awesome para íconos modernos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos inline para un diseño moderno y consistente -->
    <style>
        /* Variables CSS para mantener consistencia */
        :root {
            --color-primary: #FF5722; /* Naranja vibrante */
            --color-secondary: #4CAF50; /* Verde natural */
            --color-text: #333; /* Gris oscuro para texto */
            --color-light: #fff; /* Blanco puro */
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.15); /* Sombra moderna */
            --transition: all 0.3s ease; /* Transición suave */
        }

        /* Estilos generales del cuerpo */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f4f4f4, #e0e0e0);
            min-height: 100vh;
        }

        /* Estilos del encabezado */
        header {
            background-color: var(--color-light);
            padding: 1rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-logo img {
            max-width: 100%;
            height: 150px;
            border-radius: 15px;
            transition: var(--transition);
        }

        .header-logo img:hover {
            transform: scale(1.02);
        }

        .user-welcome {
            font-size: 1.2rem;
            color: var(--color-primary);
            font-weight: bold;
            margin: 0 1rem;
        }

        .header-buttons {
            display: flex;
            gap: 1rem;
            margin-right: 1rem;
        }

        .btn-header {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-header:hover {
            background-color: #388E3C;
            transform: translateY(-2px);
        }

        /* Contenedor principal */
        .contenedor {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: var(--color-light);
            border-radius: 15px;
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
            position: relative;
        }

        h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--color-primary);
            border-radius: 2px;
        }

        /* Estilos del formulario */
        form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        label {
            font-weight: 600;
            color: var(--color-text);
            font-size: 1.1rem;
        }

        input[type="text"],
        input[type="hidden"] {
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            transition: var(--transition);
        }

        input[type="text"]:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
            outline: none;
        }

        input[type="submit"] {
            background-color: var(--color-secondary);
            color: var(--color-light);
            padding: 0.9rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        input[type="submit"]:hover {
            background-color: #388E3C;
            transform: translateY(-2px);
        }

        /* Estilo de la notificación */
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

        .success {
            background-color: var(--color-secondary);
        }

        .error {
            background-color: #f44336;
        }

        /* Estilos responsivos */
        @media (max-width: 768px) {
            header { flex-direction: column; gap: 1rem; padding: 0.5rem; }
            .header-logo img { width: 80%; max-width: 500px; height: auto; }
            .header-buttons { flex-direction: column; width: 100%; align-items: center; }
            .btn-header { width: 80%; justify-content: center; }
            .contenedor { width: 90%; margin: 2rem auto; padding: 1.5rem; }
            h3 { font-size: 1.5rem; }
        }

        @media (max-width: 576px) {
            .contenedor { padding: 1rem; }
            input[type="text"], input[type="submit"] { font-size: 0.9rem; padding: 0.6rem; }
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo, usuario logueado y botones de navegación -->
    <header>
        <!-- Logo enlazado a home.php -->
        <div class="header-logo">
            <a href="home.php" title="Volver al panel de administración">
                <img src="imagenes/logo.jpeg" alt="San Basilio de Palenque" width="1000" height="150">
            </a>
        </div>
        <!-- Mostrar el nombre del usuario logueado -->
        <span class="user-welcome"><i class="fas fa-user"></i> ¡Hola, <?php echo $username; ?>!</span>
        <!-- Botones de navegación -->
        <div class="header-buttons">
            <a href="index.php" class="btn-header"><i class="fas fa-arrow-left"></i> Regresar a Inicio</a>
        </div>
    </header>

    <!-- Contenedor principal para el formulario de modificación -->
    <div class="contenedor">
        <h3>Modificar Producto</h3>
        <!-- Formulario para actualizar los datos del producto -->
        <form id="updateForm" action="query_update_producto.php" method="POST" onsubmit="return handleSubmit(event)">
            <!-- Campo oculto para enviar el ID del producto -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($fila['id']); ?>">
            
            <!-- Campo para el nombre del producto -->
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($fila['nombre']); ?>" required>
            
            <!-- Campo para el precio del producto -->
            <label for="precio">Precio</label>
            <input type="text" id="precio" name="precio" value="<?php echo htmlspecialchars($fila['precio']); ?>" 
                   pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número válido (ej. 1234.56)" required>
            
            <!-- Botón para enviar el formulario -->
            <input type="submit" value="MODIFICAR">
        </form>
    </div>

    <!-- Notificación para mensajes -->
    <div id="notification" class="notification"></div>

    <!-- Scripts para interactividad -->
    <script>
        // Manejar el envío del formulario de manera asíncrona
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
                    setTimeout(() => window.location.href = 'productos.php', 1500); // Redirigir tras 1.5s
                } else {
                    showNotification('Error al actualizar el producto', 'error');
                }
            })
            .catch(() => showNotification('Error de conexión', 'error'));

            return false;
        }

        // Mostrar notificaciones
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }

        // Validación en tiempo real para el campo de precio
        document.getElementById('precio').addEventListener('input', function() {
            const value = this.value;
            const regex = /^[0-9]+(\.[0-9]{0,2})?$/;
            if (!regex.test(value)) {
                this.style.borderColor = '#f44336';
            } else {
                this.style.borderColor = '#ddd';
            }
        });

        // Animación al enfocar los campos
        const inputs = document.querySelectorAll('input[type="text"]');
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