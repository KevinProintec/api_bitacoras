<?php
// db.php
$host = "100.27.228.80";
$dbname = "loginapp";
$user = "bitacoras";
$pass = "Bitacoras123%";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // No se muestra el error real al usuario por seguridad
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al conectar con la base de datos"]);
    exit;
}
?>
