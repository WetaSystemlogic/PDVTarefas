<?php
require 'auth.php';

$id = $_POST['id'] ?? 0;
$cnpj = trim($_POST['cnpj'] ?? '');
$nome = trim($_POST['nome'] ?? '');

if (!$id || $cnpj === '' || $nome === '') {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM clientes WHERE cnpj = ? AND id != ?');
$stmt->execute([$cnpj, $id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'CNPJ já cadastrado.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE clientes SET cnpj = ?, nome = ? WHERE id = ?');
$stmt->execute([$cnpj, $nome, $id]);

echo json_encode(['success' => true]);
?>