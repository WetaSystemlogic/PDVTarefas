<?php
require 'auth.php';

$id = $_POST['id'] ?? 0;

if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare('SELECT titulo, detalhes, responsavel_id, cliente_id, tipo_atendimento FROM tarefas WHERE id = ?');
$stmt->execute([$id]);
$tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarefa) {
    echo json_encode(['success' => false]);
    exit;
}

$now = date('Y-m-d H:i:s');

$stmt = $pdo->prepare('INSERT INTO tarefas (titulo, detalhes, responsavel_id, cliente_id, tipo_atendimento, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $tarefa['titulo'],
    $tarefa['detalhes'],
    $tarefa['responsavel_id'],
    $tarefa['cliente_id'],
    $tarefa['tipo_atendimento'],
    'A fazer',
    $now,
    $now
]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
?>