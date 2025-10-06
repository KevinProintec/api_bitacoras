<?php

// 🔥 Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['usuario'] ?? '';
$password = $data['contrasena'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Campos obligatorios"]);
    exit;
}

require_once __DIR__ . '/db.php'; 


try {
    

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => true,
            "message" => "Acceso concedido",
            "usuario" => $user['username'],
             "id" => $user['id']  
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Usuario o contraseña incorrectos"
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en la base de datos"]);
}
?>
