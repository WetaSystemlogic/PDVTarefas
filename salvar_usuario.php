<?php
require 'auth.php';

$nome = trim($_POST['nome'] ?? '');

if ($nome === '') {
    echo json_encode(['success' => false, 'message' => 'Nome \u00e9 obrigat\u00f3rio.']);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE nome = ?');
$stmt->execute([$nome]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Usu\u00e1rio j\u00e1 cadastrado.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO usuarios (nome) VALUES (?)');
$stmt->execute([$nome]);

echo json_encode(['success' => true]);
?>