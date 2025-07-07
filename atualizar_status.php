<?php
require 'auth.php';
require 'funcoes.php';

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';
$dataHora = $_POST['data_hora_agendamento'] ?? null;
if ($dataHora) {
    $dataHora = str_replace('T', ' ', $dataHora);
}

if ($id && $status) {
    // Atualiza a situação e registra a data/hora de alteração
    $now = date('Y-m-d H:i:s');
    if ($status === 'Agendado') {
        $stmt = $pdo->prepare('UPDATE tarefas SET status = ?, data_hora_agendamento = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$status, $dataHora, $now, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE tarefas SET status = ?, data_hora_agendamento = NULL, updated_at = ? WHERE id = ?');
        $stmt->execute([$status, $now, $id]);
    }
    registrarAlteracao($pdo, $id, $_SESSION['usuario_id'], 'Status alterado para ' . $status);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}