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

// Incluir el archivo de conexión a la base de datos ANTES de usarlo
include('db.php');

// Almacenar el nombre del usuario logueado en una variable con seguridad contra XSS
$username = htmlspecialchars($_SESSION['usuario']);

// Verificar si existe el ID de usuario en la sesión
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración para gestionar usuarios y pedidos de Sabor Colombiano">
    <title>Panel de Administración - Sabor Colombiano</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary: #FF5722;
            --color-secondary: #4CAF50;
            --color-accent: #FFC107;
            --color-text: #333333;
            --color-light: #FFFFFF;
            --color-hover: #FFF3E0;
            --border-radius: 10px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition-normal: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary), var(--color-secondary));
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: var(--color-text);
            padding-bottom: 60px;
            position: relative;
        }
        
        /* Header modernizado */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.8rem 0;
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
        
        .header-logo {
            margin-left: 2rem;
        }
        
        .header-logo img {
            max-width: 120px;
            border-radius: 50%;
            border: 3px solid var(--color-primary);
            transition: transform 0.3s ease;
            object-fit: cover;
        }
        
        .header-logo img:hover {
            transform: scale(1.05);
        }
        
        .user-welcome {
            color: var(--color-primary);
            font-weight: 600;
            font-size: 1.1rem;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            margin-right: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
        }
        
        .user-welcome i {
            margin-right: 0.5rem;
            color: var(--color-secondary);
        }
        
        .header-actions {
            display: flex;
            gap: 0.8rem;
            margin-right: 2rem;
        }
        
        /* Botones modernizados */
        .btn-nav {
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 30px;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
            transition: var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-productos {
            background-color: var(--color-accent);
            color: var(--color-text);
        }
        
        .btn-pedidos {
            background-color: var(--color-secondary);
            color: var(--color-light);
        }
        
        .btn-salir {
            background-color: var(--color-primary);
            color: var(--color-light);
        }
        
        .btn-nav:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            filter: brightness(1.05);
        }
        
        /* Contenedor principal mejorado */
        .container {
            margin-top: 8rem;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            max-width: 1200px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Título con diseño moderno */
        h1 {
            color: var(--color-primary);
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            padding-bottom: 0.8rem;
            font-size: 2.2rem;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--color-accent), var(--color-primary), var(--color-secondary));
            border-radius: 4px;
        }
        
        /* Barra de búsqueda mejorada */
        .search-bar {
            margin-bottom: 2.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            background: rgba(255, 255, 255, 0.5);
            padding: 1.2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 0.8rem 1.2rem;
            border: 2px solid rgba(255, 193, 7, 0.3);
            border-radius: 30px;
            transition: var(--transition-normal);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }
        
        .search-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 12px rgba(255, 87, 34, 0.2);
            outline: none;
        }
        
        .btn-search {
            background-color: var(--color-secondary);
            color: var(--color-light);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            font-weight: 500;
            transition: var(--transition-normal);
            font-family: 'Montserrat', sans-serif;
        }
        
        .btn-search:hover {
            background-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Tabla modernizada */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--color-secondary);
            color: var(--color-light);
            font-weight: 600;
            padding: 1.2rem 1rem;
            border: none;
            text-align: center;
            position: sticky;
            top: 0;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
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
        
        .table tbody tr:nth-child(even) {
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        .table tbody tr:hover {
            background-color: var(--color-hover);
            transform: scale(1.01);
        }
        
        .table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(255, 193, 7, 0.2);
            text-align: center;
            vertical-align: middle;
            font-size: 0.95rem;
        }
        
        .status-cell {
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .status-enabled {
            background-color: rgba(76, 175, 80, 0.15);
            color: var(--color-secondary);
        }
        
        .status-disabled {
            background-color: rgba(255, 87, 34, 0.15);
            color: var(--color-primary);
        }
        
        /* Botones de acción modernizados */
        .actions-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
            border: none;
        }
        
        .btn-editar {
            background-color: rgba(255, 193, 7, 0.2);
            color: var(--color-text);
        }
        
        .btn-editar:hover {
            background-color: var(--color-accent);
            color: var(--color-text);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-inhabilitar {
            background-color: rgba(255, 87, 34, 0.2);
            color: var(--color-primary);
        }
        
        .btn-inhabilitar:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-habilitar {
            background-color: rgba(76, 175, 80, 0.2);
            color: var(--color-secondary);
        }
        
        .btn-habilitar:hover {
            background-color: var(--color-secondary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-modificar {
            background-color: rgba(76, 175, 80, 0.2);
            color: var(--color-secondary);
        }
        
        .btn-modificar:hover {
            background-color: var(--color-secondary);
            color: var(--color-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Paginación modernizada */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination .page-item .page-link {
            color: var(--color-text);
            border: 1px solid rgba(255, 193, 7, 0.3);
            transition: var(--transition-normal);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-family: 'Montserrat', sans-serif;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
            color: var(--color-light);
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        
        .pagination .page-item .page-link:hover {
            background-color: var(--color-primary);
            color: var(--color-light);
            border-color: var(--color-primary);
        }
        
        /* Footer modernizado */
        footer {
            text-align: center;
            padding: 1.2rem;
            color: var(--color-text);
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(255, 193, 7, 0.2);
            font-family: 'Montserrat', sans-serif;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .container {
                padding: 2rem;
                margin-top: 7.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 0.8rem 0;
            }
            
            .header-logo {
                margin: 0.5rem 0;
            }
            
            .header-logo img {
                max-width: 80px;
            }
            
            .user-welcome {
                margin: 0.5rem 0;
                font-size: 1rem;
            }
            
            .header-actions {
                margin: 0.5rem 0;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .container {
                margin-top: 12rem;
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .btn-nav {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .actions-cell {
                flex-direction: column;
                gap: 0.4rem;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 1rem;
                margin-top: 14rem;
            }
            
            .search-bar {
                flex-direction: column;
                padding: 1rem;
            }
            
            .search-input, .btn-search {
                width: 100%;
            }
            
            .table td, .table th {
                padding: 0.8rem 0.5rem;
                font-size: 0.85rem;
            }
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
        <div class="header-actions">
            <!-- Botón para ir a la sección de productos -->
            <a href="productos.php" class="btn-nav btn-productos" title="Ir a la gestión de productos">
                <i class="fas fa-shopping-cart"></i>Productos
            </a>
            <!-- Botón para gestionar pedidos -->
            <a href="gestion_pedidos.php" class="btn-nav btn-pedidos" title="Gestionar pedidos">
                <i class="fas fa-box"></i>Pedidos
            </a>
            <!-- Botón para cerrar sesión -->
            <a href="logout.php" class="btn-nav btn-salir" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>Salir
            </a>
        </div>
    </header>

    <!-- Contenedor principal con la tabla de gestión de usuarios -->
    <div class="container">
        <h1>Panel de Administración de Usuarios</h1>
        
        <!-- Barra de búsqueda y filtros -->
        <div class="search-bar">
            <input type="text" class="search-input" id="searchUser" placeholder="Buscar usuario por nombre, correo o rol..." aria-label="Buscar usuario">
            <button class="btn-search" onclick="searchUsers()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
        
        <!-- Tabla responsive de usuarios -->
        <div class="table-container">
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
                            <td>
                                <div class="status-cell">
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php if ($fila['habilitado'] == 1): ?>
                                            <i class="fas fa-check-circle"></i> Habilitado
                                        <?php else: ?>
                                            <i class="fas fa-times-circle"></i> Inhabilitado
                                        <?php endif; ?>
                                    </span>
                                </div>
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
        </div>
        
        <!-- Paginación para tablas con muchos registros -->
        <nav aria-label="Paginación de usuarios">
            <ul class="pagination">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
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
                    <a class="page-link" href="#">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
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