<?php
require 'auth.php';
require 'funcoes.php';

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

if ($id && $status) {
    // Atualiza a situação e registra a data/hora de alteração
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('UPDATE tarefas SET status = ?, updated_at = ? WHERE id = ?');
    $stmt->execute([$status, $now, $id]);
    registrarAlteracao($pdo, $id, $_SESSION['usuario_id'], 'Status alterado para ' . $status);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}