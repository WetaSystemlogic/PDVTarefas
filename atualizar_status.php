<?php
require 'config.php';

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

if ($id && $status) {
    $stmt = $pdo->prepare('UPDATE tarefas SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$status, $id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
