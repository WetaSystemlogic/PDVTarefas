<?php
require 'auth.php';

$id = $_POST['id'] ?? 0;

if ($id) {
    $pdo->prepare('UPDATE tarefas SET cliente_id = NULL WHERE cliente_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM clientes WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>