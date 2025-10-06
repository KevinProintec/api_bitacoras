<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/db.php'; 
try {
    
    $stmt = $pdo->query("SELECT id, nombre, tipo FROM clientes ORDER BY nombre");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $clientes]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error"]);
}
?>