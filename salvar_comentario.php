<?php
require 'config.php';

$tarefa_id = $_POST['tarefa_id'] ?? 0;
$texto = $_POST['texto'] ?? '';

if ($tarefa_id && $texto !== '') {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO comentarios (tarefa_id, texto, created_at) VALUES (?, ?, ?)');
    $stmt->execute([$tarefa_id, $texto, $now]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>