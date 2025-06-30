<?php
require 'config.php';

$id = $_POST['id'] ?? 0;
$detalhes = $_POST['detalhes'] ?? '';
$responsavel_id = $_POST['responsavel_id'] ?: null;
$cliente_id = $_POST['cliente_id'] ?: null;
$tipo_atendimento = $_POST['tipo_atendimento'] ?? 'Remoto';

if ($id) {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('UPDATE tarefas SET detalhes = ?, responsavel_id = ?, cliente_id = ?, tipo_atendimento = ?, updated_at = ? WHERE id = ?');
    $stmt->execute([$detalhes, $responsavel_id, $cliente_id, $tipo_atendimento, $now, $id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>