<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Incluir la conexión a la base de datos
include('db.php');

// Obtener los datos actuales del usuario
$correo = $_SESSION['usuario'];
$stmt = $conexion->prepare("SELECT nombre, correo FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Manejo del formulario de actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
    $nuevo_correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

    // Validar campos
    if (empty($nuevo_nombre) || empty($nuevo_correo)) {
        $error_msg = "Todos los campos son obligatorios.";
    } elseif (!filter_var($nuevo_correo, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Por favor, ingrese un correo electrónico válido.";
    } else {
        // Actualizar los datos en la base de datos
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE correo = ?");
        $stmt->bind_param("sss", $nuevo_nombre, $nuevo_correo, $correo);

        if ($stmt->execute()) {
            // Actualizar la sesión con el nuevo correo
            $_SESSION['usuario'] = $nuevo_correo;
            $success_msg = "Perfil actualizado correctamente.";
        } else {
            $error_msg = "Error al actualizar el perfil: " . $conexion->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Perfil</h2>
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $user['nombre']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $user['correo']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>