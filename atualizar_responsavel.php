<?php
require 'config.php';

$id = $_POST['id'] ?? 0;
$nome = trim($_POST['nome'] ?? '');

if (!$id || $nome === '') {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM responsaveis WHERE nome = ? AND id != ?');
$stmt->execute([$nome, $id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Responsável já cadastrado.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE responsaveis SET nome = ? WHERE id = ?');
$stmt->execute([$nome, $id]);

echo json_encode(['success' => true]);
?>
