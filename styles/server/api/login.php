<?php
// Verificar credenciales
$usuario = $_POST['usuario'];
$contraseña_ingresada = $_POST['contraseña'];

// 1. Buscar usuario
$sql = "SELECT u.*, p.nombre, p.apellido 
        FROM usuarios u
        INNER JOIN personas p ON u.id_persona = p.id_persona
        WHERE u.usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // 2. Validar contraseña
    if (password_verify($contraseña_ingresada, $user['contraseña'])) {
        // 3. Crear sesión
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['perfil'] = $user['id_perfil'];
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["error" => "Usuario no existe"]);
}
?>