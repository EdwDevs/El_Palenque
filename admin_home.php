<?php
// Iniciar la sesión para gestionar datos del usuario logueado
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

// Almacenar el nombre del usuario logueado en una variable con seguridad
$username = htmlspecialchars($_SESSION['usuario']); // Escapar caracteres para prevenir XSS

// Incluir el archivo de conexión a la base de datos
include('db.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración para gestionar usuarios de Sabor Colombiano">
    <title>Panel de Administración - Sabor Colombiano</title>
    
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
            transition: var(--transition-normal);
        }
        
        /* Contenedor del logo en el encabezado */
        .header-logo {
            margin-left: 2rem;
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
        
        /* Mensaje de bienvenida al usuario */
        .user-welcome {
            color: var(--color-primary);
            font-weight: bold;
            font-size: 1.2rem;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            margin-right: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
        }
        
        /* Icono de usuario en el mensaje de bienvenida */
        .user-welcome i {
            margin-right: 0.5rem;
            color: var(--color-secondary);
        }
        
        /* Efecto hover para el mensaje de bienvenida */
        .user-welcome:hover {
            transform: scale(1.05);
            background-color: rgba(255, 255, 255, 1);
        }
        
        /* Botón para cerrar sesión */
        .btn-salir {
            background-color: var(--color-primary);
            color: var(--color-light);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            margin-right: 2rem;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Efecto hover para el botón de salir */
        .btn-salir:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Contenedor principal del contenido */
        .container {
            margin-top: 8rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            max-width: 1200px; /* Aumentado para mejor visualización en pantallas grandes */
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
            margin-bottom: 2rem;
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
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
            border-radius: 3px;
        }
        
        /* Barra de búsqueda y filtros */
        .search-bar {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        /* Campo de búsqueda */
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 0.75rem;
            border: 2px solid var(--color-accent);
            border-radius: var(--border-radius);
            transition: var(--transition-normal);
        }
        
        .search-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 8px rgba(255, 87, 34, 0.3);
            outline: none;
        }
        
        /* Botón de búsqueda */
        .btn-search {
            background-color: var(--color-secondary);
            color: var(--color-light);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
        }
        
        .btn-search:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
        }
        
        /* Estilos de la tabla de usuarios */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2rem;
        }
        
        /* Encabezados de la tabla */
        .table thead th {
            background-color: var(--color-secondary);
            color: var(--color-light);
            font-weight: 600;
            padding: 1rem;
            border: none;
            text-align: center;
            position: sticky;
            top: 0;
        }
        
        /* Primera columna con esquinas redondeadas a la izquierda */
        .table thead th:first-child {
            border-top-left-radius: var(--border-radius);
        }
        
        /* Última columna con esquinas redondeadas a la derecha */
        .table thead th:last-child {
            border-top-right-radius: var(--border-radius);
        }
        
        /* Filas de la tabla */
        .table tbody tr {
            transition: var(--transition-normal);
        }
        
        /* Efecto hover para las filas */
        .table tbody tr:hover {
            background-color: var(--color-hover);
            transform: scale(1.01);
        }
        
        /* Celdas de la tabla */
        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--color-accent);
            text-align: center;
            vertical-align: middle;
        }
        
        /* Celda de estado con indicador visual */
        .status-cell {
            font-weight: 600;
        }
        
        .status-enabled {
            color: var(--color-secondary);
        }
        
        .status-disabled {
            color: var(--color-primary);
        }
        
        /* Celda de acciones con botones */
        .actions-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        /* Estilos comunes para los botones de acción */
        .btn-action {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }
        
        /* Botón para editar/asignar rol */
        .btn-editar {
            background-color: var(--color-accent);
            color: var(--color-text);
        }
        
        .btn-editar:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Botón para inhabilitar usuarios */
        .btn-inhabilitar {
            background-color: var(--color-primary);
            color: var(--color-light);
        }
        
        .btn-inhabilitar:hover {
            background-color: #F44336; /* Rojo más intenso */
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Botón para habilitar usuarios */
        .btn-habilitar {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }
        
        .btn-habilitar:hover {
            background-color: #388E3C; /* Verde más intenso */
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Botón para modificar usuarios */
        .btn-modificar {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }
        
        .btn-modificar:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Paginación de la tabla */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .pagination .page-item .page-link {
            color: var(--color-text);
            border: 1px solid var(--color-accent);
            transition: var(--transition-normal);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
            color: var(--color-light);
        }
        
        .pagination .page-item .page-link:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
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
            .header-logo {
                margin-left: 1rem;
            }
            
            .header-logo img {
                max-width: 80px;
            }
            
            .user-welcome {
                font-size: 1rem;
                padding: 0.4rem 0.8rem;
            }
            
            .btn-salir {
                margin-right: 1rem;
                padding: 0.5rem 1rem;
            }
            
            .container {
                padding: 1.5rem;
                margin-top: 7rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .actions-cell {
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
            
            /* Hacer la tabla responsive en móviles */
            .table-responsive {
                overflow-x: auto;
            }
        }
        
        /* Estilos para pantallas muy pequeñas */
        @media (max-width: 576px) {
            .container {
                padding: 1rem;
                margin-top: 6rem;
            }
            
            .search-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado fijo con logo, bienvenida y botón de salir -->
    <header>
        <div class="header-logo">
            <a href="home.php" title="Ir a la página principal">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>
        <span class="user-welcome">
            <i class="fas fa-user-shield"></i>¡Hola, <?php echo $username; ?>!
        </span>
        <a href="logout.php" title="Cerrar sesión">
            <button class="btn-salir">
                <i class="fas fa-sign-out-alt"></i>Salir
            </button>
        </a>
    </header>

    <!-- Contenedor principal con la tabla de gestión de usuarios -->
    <div class="container">
        <h1>Panel de Administración de Usuarios</h1>
        
        <!-- Barra de búsqueda y filtros -->
        <div class="search-bar">
            <input type="text" class="search-input" id="searchUser" placeholder="Buscar usuario..." aria-label="Buscar usuario">
            <button class="btn-search" onclick="searchUsers()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
        
        <!-- Tabla responsive de usuarios -->
        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>Nombre Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultar todos los usuarios de la base de datos
                    $sel = $conexion->query("SELECT * FROM usuarios ORDER BY id ASC");
                    
                    // Verificar si hay usuarios para mostrar
                    if ($sel->num_rows > 0) {
                        // Iterar sobre los resultados y mostrarlos en la tabla
                        while ($fila = $sel->fetch_assoc()) {
                            // Determinar la clase CSS según el estado del usuario
                            $statusClass = $fila['habilitado'] == 1 ? 'status-enabled' : 'status-disabled';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['rol']); ?></td>
                        <td class="status-cell <?php echo $statusClass; ?>">
                            <?php if ($fila['habilitado'] == 1): ?>
                                <i class="fas fa-check-circle"></i> Habilitado
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i> Inhabilitado
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <?php if ($fila['habilitado'] == 1): ?>
                                <a href="inhabilitar.php?correo=<?php echo urlencode($fila['correo']); ?>" class="btn-action btn-inhabilitar" title="Inhabilitar usuario">
                                    <i class="fas fa-user-slash"></i> Inhabilitar
                                </a>
                            <?php else: ?>
                                <a href="habilitar.php?correo=<?php echo urlencode($fila['correo']); ?>" class="btn-action btn-habilitar" title="Habilitar usuario">
                                    <i class="fas fa-user-check"></i> Habilitar
                                </a>
                            <?php endif; ?>
                            
                            <a href="modificar_usuario.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-editar" title="Asignar rol al usuario">
                                <i class="fas fa-user-tag"></i> Asignar Rol
                            </a>
                            
                            <a href="editar_usuario.php?id=<?php echo $fila['id']; ?>" class="btn-action btn-modificar" title="Modificar datos del usuario">
                                <i class="fas fa-user-edit"></i> Modificar
                            </a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        // Mostrar mensaje si no hay usuarios
                    ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay usuarios registrados en el sistema.</td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación para tablas con muchos registros -->
        <nav aria-label="Paginación de usuarios">
            <ul class="pagination">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                </li>
                <li class="page-item active" aria-current="page">
                    <a class="page-link" href="#">1</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">2</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">3</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Pie de página con información de copyright -->
    <footer>
        <p>© 2025 Sabor Colombiano - Gestión con raíces y tradición.</p>
    </footer>

    <!-- Bootstrap JS: Incluye las funcionalidades de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personalizado para funcionalidades adicionales -->
    <script>
        // Función para buscar usuarios en la tabla
        function searchUsers() {
            // Obtener el valor de búsqueda
            const searchValue = document.getElementById('searchUser').value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');
            
            // Recorrer todas las filas de la tabla (excepto el encabezado)
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                // Buscar en cada celda de la fila
                for (let j = 0; j < cells.length - 1; j++) { // Excluir la celda de acciones
                    const cellText = cells[j].textContent.toLowerCase();
                    
                    if (cellText.includes(searchValue)) {
                        found = true;
                        break;
                    }
                }
                
                // Mostrar u ocultar la fila según el resultado de la búsqueda
                rows[i].style.display = found ? '' : 'none';
            }
        }
        
        // Escuchar eventos de teclado en el campo de búsqueda
        document.getElementById('searchUser').addEventListener('keyup', searchUsers);
        
        // Confirmar antes de inhabilitar o habilitar un usuario
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionar todos los botones de inhabilitar y habilitar
            const actionButtons = document.querySelectorAll('.btn-inhabilitar, .btn-habilitar');
            
            // Añadir evento de clic a cada botón
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Determinar el tipo de acción
                    const action = this.classList.contains('btn-inhabilitar') ? 'inhabilitar' : 'habilitar';
                    
                    // Mostrar confirmación
                    if (!confirm(`¿Estás seguro de que deseas ${action} este usuario?`)) {
                        e.preventDefault(); // Cancelar la acción si el usuario no confirma
                    }
                });
            });
        });
    </script>
</body>
</html>