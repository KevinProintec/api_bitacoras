<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");


require_once __DIR__ . '/db.php'; 
try {
    

    $stmt = $pdo->query("SELECT id, nombre FROM acompanantes ORDER BY nombre");
    $acompanantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $acompanantes
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en BD"]);
}
?>