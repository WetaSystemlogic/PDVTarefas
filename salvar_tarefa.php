<?php
require 'config.php';

$titulo = $_POST['titulo'] ?? '';
$detalhes = $_POST['detalhes'] ?? '';
$responsavel_id = $_POST['responsavel_id'] ?: null;
$cliente_id = $_POST['cliente_id'] ?: null;
$tipo_atendimento = $_POST['tipo_atendimento'] ?? 'Remoto';
$created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');

if ($created_at) {
    $created_at = str_replace('T', ' ', $created_at);
}
$updated_at = $created_at;

if ($titulo) {
    $stmt = $pdo->prepare('INSERT INTO tarefas (titulo, detalhes, responsavel_id, cliente_id, tipo_atendimento, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$titulo, $detalhes, $responsavel_id, $cliente_id, $tipo_atendimento, 'A fazer', $created_at, $updated_at]);
}

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
if ($isAjax) {
    echo json_encode(['success' => true]);
    exit;
}

header('Location: index.php');
exit;
?>