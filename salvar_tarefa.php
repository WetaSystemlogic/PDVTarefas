<?php
require 'config.php';

$titulo = $_POST['titulo'] ?? '';
$detalhes = $_POST['detalhes'] ?? '';
$responsavel_id = $_POST['responsavel_id'] ?: null;
$cliente_id = $_POST['cliente_id'] ?: null;

if ($titulo) {
    $stmt = $pdo->prepare('INSERT INTO tarefas (titulo, detalhes, responsavel_id, cliente_id, status) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$titulo, $detalhes, $responsavel_id, $cliente_id, 'A fazer']);
}

header('Location: index.php');
exit;
?>