<?php
require 'auth.php';
require 'funcoes.php';

$titulo = $_POST['titulo'] ?? '';
$detalhes = $_POST['detalhes'] ?? '';
$responsavel_id = $_POST['responsavel_id'] ?: null;
$cliente_id = $_POST['cliente_id'] ?: null;
$tipo_atendimento = $_POST['tipo_atendimento'] ?? 'Remoto';
$data_hora_agendamento = null;
$created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');

if ($created_at) {
    $created_at = str_replace('T', ' ', $created_at);
}
$updated_at = $created_at;

if ($titulo) {
    $stmt = $pdo->prepare('INSERT INTO tarefas (titulo, detalhes, responsavel_id, cliente_id, tipo_atendimento, data_hora_agendamento, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$titulo, $detalhes, $responsavel_id, $cliente_id, $tipo_atendimento, $data_hora_agendamento, 'A fazer', $created_at, $updated_at]);
    $tarefaId = $pdo->lastInsertId();
    registrarAlteracao($pdo, $tarefaId, $_SESSION['usuario_id'], 'Tarefa criada');
}

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
if ($isAjax) {
    echo json_encode(['success' => true]);
    exit;
}

header('Location: index.php');
exit;