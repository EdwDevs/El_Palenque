<?php
// server/api/registro.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Recibir datos
$data = json_decode(file_get_contents('php://input'), true);

// Validación básica
if (empty($data['usuario']) || empty($data['contraseña'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Campos obligatorios faltantes']));
}

// Conectar a MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Insertar en tabla personas
$stmt = $conn->prepare("INSERT INTO personas (nombre, apellido, correo) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $data['nombre'], $data['apellido'], $data['email']);
$stmt->execute();
$id_persona = $conn->insert_id;

// Insertar en tabla usuarios
$contraseña_hash = password_hash($data['contraseña'], PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO usuarios (id_persona, usuario, contraseña) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $id_persona, $data['usuario'], $contraseña_hash);
$stmt->execute();

echo json_encode(['success' => true]);
?>