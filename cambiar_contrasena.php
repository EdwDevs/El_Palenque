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

// Manejo del formulario de cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_SESSION['usuario'];
    $contraseña_actual = $_POST['contraseña_actual'];
    $nueva_contraseña = $_POST['nueva_contraseña'];
    $confirmar_contraseña = $_POST['confirmar_contraseña'];

    // Validar campos
    if (empty($contraseña_actual) || empty($nueva_contraseña) || empty($confirmar_contraseña)) {
        $error_msg = "Todos los campos son obligatorios.";
    } elseif ($nueva_contraseña !== $confirmar_contraseña) {
        $error_msg = "Las contraseñas no coinciden.";
    } elseif (strlen($nueva_contraseña) < 8) {
        $error_msg = "La nueva contraseña debe tener al menos 8 caracteres.";
    } else {
        // Verificar la contraseña actual
        $stmt = $conexion->prepare("SELECT contraseña FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($contraseña_actual, $user['contraseña'])) {
            $error_msg = "La contraseña actual es incorrecta.";
        } else {
            // Hashear la nueva contraseña
            $hashed_password = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $stmt = $conexion->prepare("UPDATE usuarios SET contraseña = ? WHERE correo = ?");
            $stmt->bind_param("ss", $hashed_password, $correo);

            if ($stmt->execute()) {
                $success_msg = "Contraseña actualizada correctamente.";
            } else {
                $error_msg = "Error al actualizar la contraseña: " . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Cambiar Contraseña</h2>
        <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="contraseña_actual" class="form-label">Contraseña Actual</label>
                <input type="password" class="form-control" id="contraseña_actual" name="contraseña_actual" required>
            </div>
            <div class="mb-3">
                <label for="nueva_contraseña" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña" required>
            </div>
            <div class="mb-3">
                <label for="confirmar_contraseña" class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
        </form>
    </div>
</body>

</html>