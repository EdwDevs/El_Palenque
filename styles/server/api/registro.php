<?php
header('Content-Type: application/json');
require_once '../../config.php';

$data = json_decode(file_get_contents('php://input'), true);

$nombre = $data['nombre'];
$email = $data['email'];
$usuario = $data['usuario'];
$contraseña = password_hash($data['contraseña'], PASSWORD_DEFAULT);

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario OR email = :email");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Usuario o correo ya registrado']);
        exit;
    }

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contraseña, id_perfil) 
                           VALUES (:usuario, :contraseña, 2)"); // 2 = perfil de usuario normal

    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':contraseña', $contraseña);
    $stmt->execute();

    // Insertar en tabla personas
    $id_usuario = $conn->lastInsertId();
    $stmt = $conn->prepare("INSERT INTO personas (nombre, correo) VALUES (:nombre, :email)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en el registro: ' . $e->getMessage()]);
}
?>