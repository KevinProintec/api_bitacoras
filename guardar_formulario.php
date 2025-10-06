<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Validar campos obligatorios básicos
if (!isset($data['piloto_id'], $data['vehiculo_id'], $data['cliente'], $data['lugar'], $data['actividad'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

$nombreCliente = trim($data['cliente']);
if (empty($nombreCliente)) {
    echo json_encode(["success" => false, "message" => "Nombre del cliente no válido"]);
    exit;
}

$actividad = $data['actividad'];

// Validación específica por actividad
if ($actividad !== "Deposito" && (!isset($data['tipo_documento']) || !isset($data['numero_documento']))) {
    echo json_encode(["success" => false, "message" => "Tipo y número de documento son obligatorios para esta actividad"]);
    exit;
}

if ($actividad === "Otros") {
    if (!isset($data['comentario_piloto']) || trim($data['comentario_piloto']) === '') {
        echo json_encode(["success" => false, "message" => "El comentario es obligatorio para la actividad 'Otros'"]);
        exit;
    }
}

require_once __DIR__ . '/db.php'; 

try {
    

    // 🔍 Buscar el cliente por nombre
    $stmtCliente = $pdo->prepare("SELECT id, tipo FROM clientes WHERE nombre = ? LIMIT 1");
    $stmtCliente->execute([$nombreCliente]);
    $clienteData = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    if (!$clienteData) {
        echo json_encode([
            "success" => false,
            "message" => "El cliente '$nombreCliente' no existe en la base de datos. Por favor, agrégalo primero."
        ]);
        exit;
    }

    $cliente_id = $clienteData['id'];

    // Preparar los valores a insertar
    $medioPago = $data['medio_pago'] ?? null;
    $numeroRecibo = $data['numero_recibo'] ?? null;
    $numeroContrasena = $data['numero_contrasena'] ?? null;
    $banco = $data['banco'] ?? null;
    $tipoCliente = $data['tipo_cliente'] ?? null;
    $comentario_piloto = ($actividad === "Otros") ? trim($data['comentario_piloto']) : null;

    // Tipo y número de documento: solo si no es Depósito
    $tipoDocumento = ($actividad !== "Deposito") ? ($data['tipo_documento'] ?? null) : null;
    $numeroDocumento = ($actividad !== "Deposito") ? ($data['numero_documento'] ?? null) : null;

    // Insertar formulario
    $stmt = $pdo->prepare("
        INSERT INTO formularios (
            piloto_id, vehiculo_id, cliente_id, cliente_nombre, lugar_entrega,
            tipo_documento, numero_documento, actividad,
            medio_pago, numero_recibo, numero_contrasena, banco, tipo_cliente,
            comentario, comentario_piloto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['piloto_id'],
        $data['vehiculo_id'],
        $cliente_id,
        $nombreCliente,
        $data['lugar'],
        $tipoDocumento,
        $numeroDocumento,
        $actividad,
        $medioPago,
        $numeroRecibo,
        $numeroContrasena,
        $banco,
        $tipoCliente,
        null, // 🟡 Reservado para comentarios del admin (no se usa aquí)
        $comentario_piloto // ✅ Comentario del piloto
    ]);

    $formularioId = $pdo->lastInsertId();

    // Insertar acompañantes (si existen)
    if (!empty($data['acompanantes_ids']) && is_array($data['acompanantes_ids'])) {
        $stmt2 = $pdo->prepare("INSERT INTO formularios_acompanantes (formulario_id, acompanante_id) VALUES (?, ?)");
        foreach ($data['acompanantes_ids'] as $id) {
            if (is_numeric($id)) {
                $stmt2->execute([$formularioId, (int)$id]);
            }
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Formulario guardado",
        "formulario_id" => $formularioId,
        "cliente_id" => $cliente_id
    ]);

} catch (PDOException $e) {
    error_log("Error guardar_formulario (BD): " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error en base de datos"
    ]);
} catch (Exception $e) {
    error_log("Error general guardar_formulario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor"
    ]);
}
?>