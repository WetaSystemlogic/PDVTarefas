<?php
require 'config.php';

$id = $_POST['id'] ?? 0;

if ($id) {
    $pdo->prepare('DELETE FROM comentarios WHERE tarefa_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM subtarefas WHERE tarefa_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM tarefas WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>