<?php
// Iniciar sesión para verificar autenticación
session_start();

// Verificar si el usuario está autenticado; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Verificar si el usuario tiene rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Redirigir a usuarios sin privilegios de administrador
    header("Location: user_home.php");
    exit();
}

// Incluir archivo de conexión a la base de datos
include('db.php');

// Verificar si se proporcionó un ID válido
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID de usuario no válido'); window.location.href='admin_home.php';</script>";
    exit();
}

// Obtener el ID del usuario a editar y sanitizarlo
$id = intval($_GET['id']);

// Preparar consulta con sentencia preparada para prevenir inyección SQL
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si el usuario existe
if ($resultado->num_rows === 0) {
    echo "<script>alert('Usuario no encontrado'); window.location.href='admin_home.php';</script>";
    exit();
}

// Obtener los datos del usuario
$fila = $resultado->fetch_assoc();

// Cerrar la sentencia preparada
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Formulario para editar datos de usuarios de Sabor Colombiano">
    <title>Editar Usuario - Sabor Colombiano</title>

    <!-- Bootstrap CSS: Framework para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Montserrat: Tipografía principal del sitio -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome: Biblioteca de iconos para mejorar la interfaz -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos personalizados: Define la apariencia específica de la aplicación -->
    <style>
    /* Variables CSS para mantener consistencia en colores y valores */
    :root {
        --color-primary: #FF5722;
        --color-secondary: #4CAF50;
        --color-accent: #FFC107;
        --color-text: #333333;
        --color-light: #FFFFFF;
        --color-hover: #FFF3E0;
        --border-radius: 10px;
        --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        --transition-normal: all 0.3s ease;
    }

    /* Estilos generales del cuerpo de la página */
    body {
        /* Fondo con degradado de colores que representan la bandera colombiana */
        background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        font-family: 'Montserrat', sans-serif;
        color: var(--color-text);
        padding-bottom: 60px;
        /* Espacio para el footer */
        position: relative;
    }

    /* Estilos del encabezado fijo en la parte superior */
    header {
        background: rgba(255, 255, 255, 0.95);
        padding: 1rem 0;
        box-shadow: var(--box-shadow);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Botón para volver al panel de administración */
    .btn-volver {
        background-color: var(--color-accent);
        color: var(--color-text);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        margin-left: 2rem;
        transition: var(--transition-normal);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    /* Efecto hover para el botón de volver */
    .btn-volver:hover {
        background-color: var(--color-primary);
        color: var(--color-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Contenedor del logo en el encabezado */
    .header-logo {
        text-align: center;
        flex-grow: 1;
    }

    /* Estilos de la imagen del logo */
    .header-logo img {
        max-width: 120px;
        border-radius: var(--border-radius);
        border: 3px solid var(--color-primary);
        transition: transform 0.3s ease;
    }

    /* Efecto hover para la imagen del logo */
    .header-logo img:hover {
        transform: scale(1.05);
    }

    /* Espaciador para equilibrar el header */
    .header-spacer {
        width: 150px;
    }

    /* Contenedor principal del formulario */
    .edit-container {
        width: 100%;
        max-width: 600px;
        margin: 8rem auto 2rem;
        padding: 2.5rem;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: var(--box-shadow);
        animation: fadeIn 0.5s ease-in-out;
    }

    /* Animación de aparición suave */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Título principal de la página */
    h2 {
        color: var(--color-primary);
        font-weight: 700;
        text-align: center;
        margin-bottom: 1rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    /* Línea decorativa debajo del título */
    h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
        border-radius: 3px;
    }

    /* Información del usuario que se está editando */
    .user-info {
        text-align: center;
        color: var(--color-secondary);
        font-weight: 600;
        margin-bottom: 2rem;
        padding: 0.5rem;
        background-color: var(--color-hover);
        border-radius: var(--border-radius);
    }

    /* Etiquetas de los campos del formulario */
    .form-label {
        color: var(--color-secondary);
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Campos de entrada del formulario */
    .form-control {
        border: 2px solid var(--color-accent);
        border-radius: var(--border-radius);
        padding: 0.75rem;
        transition: var(--transition-normal);
        font-size: 1rem;
    }

    /* Efecto focus para los campos de entrada */
    .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
        outline: none;
    }

    /* Contenedor de los botones de acción */
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    /* Botón para actualizar los datos */
    .btn-actualizar {
        background-color: var(--color-secondary);
        border: none;
        padding: 0.75rem;
        font-weight: 600;
        color: white;
        border-radius: var(--border-radius);
        flex: 1;
        transition: var(--transition-normal);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
    }

    /* Efecto hover para el botón de actualizar */
    .btn-actualizar:hover {
        background-color: var(--color-primary);
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Botón para cancelar la operación */
    .btn-cancelar {
        background-color: #6c757d;
        border: none;
        padding: 0.75rem;
        font-weight: 600;
        color: white;
        border-radius: var(--border-radius);
        flex: 1;
        transition: var(--transition-normal);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    /* Efecto hover para el botón de cancelar */
    .btn-cancelar:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Mensaje de ayuda para los campos */
    .form-text {
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    /* Pie de página */
    footer {
        text-align: center;
        padding: 1rem;
        color: var(--color-text);
        font-size: 0.9rem;
        background: rgba(255, 255, 255, 0.8);
        position: absolute;
        bottom: 0;
        width: 100%;
        border-top: 1px solid rgba(255, 193, 7, 0.3);
    }

    /* Estilos responsivos para dispositivos móviles */
    @media (max-width: 768px) {
        .edit-container {
            padding: 1.5rem;
            margin-top: 7rem;
            width: 90%;
        }

        .btn-volver {
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .header-logo img {
            max-width: 80px;
        }

        h2 {
            font-size: 1.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
    </style>
</head>

<body>
    <!-- Encabezado con logo y botón para volver -->
    <header>
        <a href="admin_home.php" class="btn-volver" title="Volver al panel de administración">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div class="header-logo">
            <a href="home.php" title="Ir a la página principal">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>
        <div class="header-spacer"></div> <!-- Espaciador para equilibrar el header -->
    </header>

    <!-- Contenedor principal con el formulario -->
    <div class="edit-container">
        <h2>Editar Datos del Usuario</h2>

        <!-- Información del usuario que se está editando -->
        <div class="user-info">
            <i class="fas fa-user-edit"></i> Editando usuario: <?php echo htmlspecialchars($fila['nombre']); ?>
        </div>

        <!-- Formulario para actualizar los datos del usuario -->
        <form action="actualizar_usuario.php" method="POST" id="formEditar">
            <!-- Campo oculto con el ID del usuario -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <!-- Campo para el nombre -->
            <div class="mb-3">
                <label for="nombre" class="form-label">
                    <i class="fas fa-user"></i> Nombre
                </label>
                <input type="text" class="form-control" id="nombre" name="nombre"
                    value="<?php echo htmlspecialchars($fila['nombre']); ?>" required minlength="3" maxlength="50"
                    aria-describedby="nombreHelp">
                <div id="nombreHelp" class="form-text">
                    <i class="fas fa-info-circle"></i> Ingrese el nombre completo del usuario.
                </div>
            </div>

            <!-- Campo para el correo electrónico -->
            <div class="mb-3">
                <label for="correo" class="form-label">
                    <i class="fas fa-envelope"></i> Correo Electrónico
                </label>
                <input type="email" class="form-control" id="correo" name="correo"
                    value="<?php echo htmlspecialchars($fila['correo']); ?>" required
                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" aria-describedby="correoHelp">
                <div id="correoHelp" class="form-text">
                    <i class="fas fa-info-circle"></i> Ingrese un correo electrónico válido.
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="action-buttons">
                <a href="admin_home.php" class="btn-cancelar" title="Cancelar y volver">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-actualizar" title="Guardar cambios">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Pie de página con información de copyright -->
    <footer>
        <p>© 2025 Sabor Colombiano - Gestión con raíces y tradición.</p>
    </footer>

    <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script personalizado para validación y confirmación -->
    <script>
    // Cuando el documento esté cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener referencia al formulario
        const form = document.getElementById('formEditar');

        // Añadir evento de envío al formulario
        form.addEventListener('submit', function(e) {
            // Prevenir envío por defecto
            e.preventDefault();

            // Obtener los valores de los campos
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();

            // Validar nombre
            if (nombre.length < 3) {
                alert('El nombre debe tener al menos 3 caracteres.');
                return;
            }

            // Validar correo con expresión regular
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(correo)) {
                alert('Por favor, ingrese un correo electrónico válido.');
                return;
            }

            // Confirmar la acción
            if (confirm('¿Estás seguro de que deseas guardar los cambios?')) {
                // Si confirma, enviar el formulario
                this.submit();
            }
        });
    });
    </script>
</body>

</html>