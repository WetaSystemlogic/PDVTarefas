<?php
require 'auth.php';
require 'funcoes.php';

$id = $_POST['id'] ?? 0;
$titulo = $_POST['titulo'] ?? '';
$detalhes = $_POST['detalhes'] ?? '';
$responsavel_id = $_POST['responsavel_id'] ?: null;
$cliente_id = $_POST['cliente_id'] ?: null;
$tipo_atendimento = $_POST['tipo_atendimento'] ?? 'Remoto';

if ($id) {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('UPDATE tarefas SET titulo = ?, detalhes = ?, responsavel_id = ?, cliente_id = ?, tipo_atendimento = ?, updated_at = ? WHERE id = ?');
    $stmt->execute([$titulo, $detalhes, $responsavel_id, $cliente_id, $tipo_atendimento, $now, $id]);
    registrarAlteracao($pdo, $id, $_SESSION['usuario_id'], 'Detalhes atualizados');
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>