<?php
require 'auth.php';

$id = $_POST['id'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>