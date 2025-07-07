<?php
require 'auth.php';
require 'funcoes.php';

$id = $_POST['id'] ?? 0;

if ($id) {
    registrarAlteracao($pdo, $id, $_SESSION['usuario_id'], 'Tarefa excluída');
    $pdo->prepare('DELETE FROM comentarios WHERE tarefa_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM subtarefas WHERE tarefa_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM tarefas WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>