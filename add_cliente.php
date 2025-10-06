<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nombre']) || !isset($data['tipo'])) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$nombre = trim($data['nombre']);
$tipo = $data['tipo'];

if (!in_array($tipo, ['contado', 'credito'])) {
    echo json_encode(["success" => false, "message" => "Tipo inválido"]);
    exit;
}

require_once __DIR__ . '/db.php'; 


try {
    

    $stmt = $pdo->prepare("INSERT INTO clientes (nombre, tipo) VALUES (?, ?)");
    $stmt->execute([$nombre, $tipo]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "id" => $id,
        "message" => "Cliente registrado"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en BD"]);
}
?>