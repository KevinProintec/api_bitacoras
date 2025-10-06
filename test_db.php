<?php
$data = [
    'piloto_id' => 1,
    'vehiculo_id' => 1,
    'cliente' => 'Empaques de calidad',
    'lugar' => 'ciudad',
    'tipo_documento' => 'Factura',
    'numero_documento' => '1778',
    'actividad' => 'Entrega',
    'medio_pago' => 'Cheque',
    'numero_recibo' => '12366557'
];

require_once __DIR__ . '/db.php'; 
// Simula el INSERT
try {
 

    $stmt = $pdo->prepare("
        INSERT INTO formularios (
            piloto_id, vehiculo_id, cliente_nombre, lugar_entrega,
            tipo_documento, numero_documento, actividad,
            medio_pago, numero_recibo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['piloto_id'],
        $data['vehiculo_id'],
        $data['cliente'],
        $data['lugar'],
        $data['tipo_documento'],
        $data['numero_documento'],
        $data['actividad'],
        $data['medio_pago'],
        $data['numero_recibo']
    ]);

    echo "Éxito";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>