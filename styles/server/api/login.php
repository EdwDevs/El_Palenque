<?php
header('Content-Type: application/json');
require_once '../../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$usuario = $data['usuario'];
$contraseña = $data['contraseña'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($contraseña, $user['contraseña'])) {
            session_start();
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['id_perfil'] = $user['id_perfil'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario no existe']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>