<?php
require 'config.php';

$cnpj = trim($_POST['cnpj'] ?? '');
$nome = trim($_POST['nome'] ?? '');

if ($cnpj === '' || $nome === '') {
    echo json_encode(['success' => false, 'message' => 'CNPJ e nome s\u00e3o obrigat\u00f3rios.']);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM clientes WHERE cnpj = ?');
$stmt->execute([$cnpj]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'CNPJ j\u00e1 cadastrado.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO clientes (cnpj, nome) VALUES (?, ?)');
$stmt->execute([$cnpj, $nome]);

echo json_encode(['success' => true]);
?>
