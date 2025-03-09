<?php
// Iniciar la sesión para gestionar datos del usuario logueado
session_start();

// Verificar si el usuario está autenticado; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Verificar si el usuario tiene rol de administrador; si no, redirigir a la página de usuario estándar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: user_home.php");
    exit();
}

// Almacenar el nombre del usuario logueado en una variable con seguridad contra XSS
$username = htmlspecialchars($_SESSION['usuario']);
if (isset($_SESSION['usuario']) && !isset($_SESSION['usuario_id'])) {
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $_SESSION['usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['usuario_id'] = $row['id'];
    }
    $stmt->close();
}

// Incluir el archivo de conexión a la base de datos (asegúrate de que db.php esté configurado correctamente)
include('db.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración para gestionar usuarios y pedidos de Sabor Colombiano">
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
            --color-primary: #FF5722; /* Naranja vibrante */
            --color-secondary: #4CAF50; /* Verde natural */
            --color-accent: #FFC107; /* Amarillo cálido */
            --color-text: #333333; /* Gris oscuro para texto */
            --color-light: #FFFFFF; /* Blanco puro */
            --color-hover: #FFF3E0; /* Fondo claro para hover */
            --border-radius: 10px; /* Bordes redondeados */
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); /* Sombra suave */
            --transition-normal: all 0.3s ease; /* Transición estándar */
        }
        
        /* Estilos generales del cuerpo de la página */
        body {
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: var(--color-text);
            padding-bottom: 60px; /* Espacio para el footer */
            position: relative;
        }
        
        /* Estilos del encabezado fijo */
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
        
        .header-logo img:hover {
            transform: scale(1.05); /* Efecto de aumento al pasar el ratón */
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
        
        .user-welcome i {
            margin-right: 0.5rem;
            color: var(--color-secondary);
        }
        
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
        
        .btn-salir:hover {
            background-color: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Botón para ir a la sección de productos */
        .btn-productos {
            background-color: var(--color-accent);
            color: var(--color-text);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-productos:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Nuevo botón para gestionar pedidos */
        .btn-pedidos {
            background-color: var(--color-secondary);
            color: var(--color-light);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-pedidos:hover {
            background-color: var(--color-primary);
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
            max-width: 1200px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Título principal */
        h1 {
            color: var(--color-primary);
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
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
        
        /* Estilos de la tabla */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2rem;
        }
        
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
        
        .table thead th:first-child {
            border-top-left-radius: var(--border-radius);
        }
        
        .table thead th:last-child {
            border-top-right-radius: var(--border-radius);
        }
        
        .table tbody tr {
            transition: var(--transition-normal);
        }
        
        .table tbody tr:hover {
            background-color: var(--color-hover);
            transform: scale(1.01);
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--color-accent);
            text-align: center;
            vertical-align: middle;
        }
        
        .status-cell {
            font-weight: 600;
        }
        
        .status-enabled {
            color: var(--color-secondary);
        }
        
        .status-disabled {
            color: var(--color-primary);
        }
        
        .actions-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
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
        
        .btn-inhabilitar {
            background-color: var(--color-primary);
            color: var(--color-light);
        }
        
        .btn-inhabilitar:hover {
            background-color: #F44336;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .btn-habilitar {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }
        
        .btn-habilitar:hover {
            background-color: #388E3C;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .btn-modificar {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }
        
        .btn-modificar:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
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
        
        @media (max-width: 768px) {
            .header-logo { margin-left: 1rem; }
            .header-logo img { max-width: 80px; }
            .user-welcome { font-size: 1rem; padding: 0.4rem 0.8rem; }
            .btn-salir, .btn-productos, .btn-pedidos { margin-right: 1rem; padding: 0.5rem 1rem; }
            .container { padding: 1.5rem; margin-top: 7rem; }
            h1 { font-size: 1.5rem; }
            .actions-cell { flex-direction: column; gap: 0.3rem; }
            .btn-action { width: 100%; justify-content: center; padding: 0.4rem 0.8rem; font-size: 0.9rem; }
            .table-responsive { overflow-x: auto; }
        }
        
        @media (max-width: 576px) {
            .container { padding: 1rem; margin-top: 6rem; }
            .search-bar { flex-direction: column; }
        }
    </style>
</head>
<body>
    <!-- Encabezado fijo con logo, bienvenida y botones -->
    <header>
        <div class="header-logo">
            <a href="index.php" title="Ir a la página principal">
                <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120">
            </a>
        </div>
        <span class="user-welcome">
            <i class="fas fa-user-shield"></i>¡Hola, <?php echo $username; ?>!
        </span>
        <div>
            <!-- Botón para ir a la sección de productos -->
            <a href="productos.php" title="Ir a la gestión de productos">
                <button class="btn-productos">
                    <i class="fas fa-shopping-cart"></i>Productos
                </button>
            </a>
            <!-- Nuevo botón para gestionar pedidos -->
            <a href="gestion_pedidos.php" title="Gestionar pedidos">
                <button class="btn-pedidos">
                    <i class="fas fa-box"></i>Pedidos
                </button>
            </a>
            <!-- Botón para cerrar sesión -->
            <a href="logout.php" title="Cerrar sesión">
                <button class="btn-salir">
                    <i class="fas fa-sign-out-alt"></i>Salir
                </button>
            </a>
        </div>
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
                    
                    if ($sel->num_rows > 0) {
                        while ($fila = $sel->fetch_assoc()) {
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

    <!-- Pie de página -->
    <footer>
        <p>© 2025 Sabor Colombiano - Gestión con raíces y tradición.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personalizado -->
    <script>
        // Función para buscar usuarios en la tabla
        function searchUsers() {
            const searchValue = document.getElementById('searchUser').value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) {
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(searchValue)) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }
        
        // Escuchar eventos de teclado en el campo de búsqueda
        document.getElementById('searchUser').addEventListener('keyup', searchUsers);
        
        // Confirmar antes de inhabilitar o habilitar un usuario
        document.addEventListener('DOMContentLoaded', function() {
            const actionButtons = document.querySelectorAll('.btn-inhabilitar, .btn-habilitar');
            
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const action = this.classList.contains('btn-inhabilitar') ? 'inhabilitar' : 'habilitar';
                    if (!confirm(`¿Estás seguro de que deseas ${action} este usuario?`)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>