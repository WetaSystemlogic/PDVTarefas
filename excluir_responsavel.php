<?php
require 'auth.php';

$id = $_POST['id'] ?? 0;

if ($id) {
    $pdo->prepare('UPDATE tarefas SET responsavel_id = NULL WHERE responsavel_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM responsaveis WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>