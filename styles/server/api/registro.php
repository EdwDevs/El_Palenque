<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// ========== VALIDACIONES BACKEND ==========
// 1. Obtener y verificar datos
$data = json_decode(file_get_contents('php://input'), true);

if (
    empty($data['nombre']) || 
    empty($data['email']) || 
    empty($data['usuario']) || 
    empty($data['contraseña'])
) {
    http_response_code(400);
    die(json_encode(['error' => 'Faltan campos obligatorios']));
}

// 2. Validar formato de email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['error' => 'Formato de email inválido']));
}

// 3. Conectar a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

// 4. Verificar usuario existente
$stmt = $conn->prepare("SELECT usuario FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $data['usuario']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    die(json_encode(['error' => 'El usuario ya existe']));
}

// 5. Insertar en tabla 'personas'
try {
    $stmt = $conn->prepare("INSERT INTO personas (nombre, correo) VALUES (?, ?)");
    $stmt->bind_param("ss", $data['nombre'], $data['email']);
    $stmt->execute();
    $id_persona = $conn->insert_id;
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Error al crear el perfil']));
}

// 6. Insertar en tabla 'usuarios'
try {
    $contraseña_hash = password_hash($data['contraseña'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO usuarios (id_persona, usuario, contraseña) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_persona, $data['usuario'], $contraseña_hash);
    $stmt->execute();
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Error al crear el usuario']));
}

// Éxito
echo json_encode(['success' => true]);
?>