<?php
require 'auth.php';

$id = $_POST['id'] ?? 0;
$nome = trim($_POST['nome'] ?? '');

if (!$id || $nome === '') {
    echo json_encode(['success' => false, 'message' => 'Dados inv\u00e1lidos.']);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE nome = ? AND id != ?');
$stmt->execute([$nome, $id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Usu\u00e1rio j\u00e1 cadastrado.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE usuarios SET nome = ? WHERE id = ?');
$stmt->execute([$nome, $id]);

echo json_encode(['success' => true]);
?>