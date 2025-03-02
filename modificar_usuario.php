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
if (!isset($_REQUEST['id']) || empty($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
    echo "<script>alert('ID de usuario no válido'); window.location.href='admin_home.php';</script>";
    exit();
}

// Obtener el ID del usuario a modificar y sanitizarlo
$id = intval($_REQUEST['id']);

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

// Almacenar el nombre del usuario para mostrar en el formulario
$nombreUsuario = htmlspecialchars($fila['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Formulario para asignar rol a usuarios de Sabor Colombiano">
    <title>Asignar Rol - Sabor Colombiano</title>
    
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
            padding-bottom: 60px; /* Espacio para el footer */
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
        
        /* Contenedor del logo en el encabezado */
        .header-logo {
            margin-left: 2rem;
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
        
        /* Botón para volver al panel de administración */
        .btn-volver {
            background-color: var(--color-accent);
            color: var(--color-text);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            margin-right: 2rem;
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
        
        /* Contenedor principal del formulario */
        .container {
            width: 100%;
            max-width: 500px;
            margin: 8rem auto 2rem;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            animation: fadeIn 0.5s ease-in-out;
        }
        
        /* Animación de aparición suave */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Título principal de la página */
        h1 {
            color: var(--color-primary);
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        /* Línea decorativa debajo del título */
        h1::after {
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
        
        /* Subtítulo con el nombre del usuario */
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
        .form-control, .form-select {
            border: 2px solid var(--color-accent);
            border-radius: var(--border-radius);
            padding: 0.75rem;
            transition: var(--transition-normal);
            font-size: 1rem;
        }
        
        /* Efecto focus para los campos de entrada */
        .form-control:focus, .form-select:focus {
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
        
        /* Botón para actualizar el rol */
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
            .container {
                padding: 1.5rem;
                margin-top: 7rem;
                width: 90%;
            }
            
            .header-logo {
                margin-left: 1rem;
            }
            
            .header-logo img {
                max-width: 80px;
            }
            
            .btn-volver {
                margin-right: 1rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            h1 {
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
        <div style="width: 150px;"></div> <!-- Espaciador para equilibrar el header -->
    </header>

    <!-- Contenedor principal con el formulario -->
    <div class="container">
        <h1>Asignar Rol de Usuario</h1>
        
        <!-- Información del usuario que se está modificando -->
        <div class="user-info">
            <i class="fas fa-user"></i> Usuario: <?php echo $nombreUsuario; ?>
        </div>
        
        <!-- Formulario para actualizar el rol -->
        <form action="actualizar_rol.php" method="POST" id="formRol">
            <!-- Campo oculto con el ID del usuario -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <!-- Selección del rol -->
            <div class="mb-4">
                <label for="rol" class="form-label">
                    <i class="fas fa-user-tag"></i> Seleccionar Rol
                </label>
                <select class="form-select" id="rol" name="rol" required aria-describedby="rolHelp">
                    <option value="" disabled>Seleccione un rol</option>
                    <option value="admin" <?php echo $fila['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="usuario" <?php echo $fila['rol'] === 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                </select>
                <div id="rolHelp" class="form-text mt-2">
                    <i class="fas fa-info-circle"></i> El rol determina los permisos que tendrá el usuario en el sistema.
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="action-buttons">
                <a href="admin_home.php" class="btn-cancelar" title="Cancelar y volver">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-actualizar" title="Guardar cambios">
                    <i class="fas fa-save"></i> Actualizar Rol
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
            const form = document.getElementById('formRol');
            
            // Añadir evento de envío al formulario
            form.addEventListener('submit', function(e) {
                // Prevenir envío por defecto
                e.preventDefault();
                
                // Obtener el rol seleccionado
                const rolSeleccionado = document.getElementById('rol').value;
                const nombreUsuario = '<?php echo $nombreUsuario; ?>';
                
                // Confirmar la acción
                if (confirm(`¿Estás seguro de que deseas asignar el rol "${rolSeleccionado}" al usuario "${nombreUsuario}"?`)) {
                    // Si confirma, enviar el formulario
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>