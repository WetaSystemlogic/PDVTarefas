<?php
function registrarAlteracao(PDO $pdo, $tarefaId, $usuarioId, $descricao){
    $stmt = $pdo->prepare('INSERT INTO alteracoes (tarefa_id, usuario_id, descricao, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$tarefaId, $usuarioId, $descricao, date('Y-m-d H:i:s')]);
}
?>