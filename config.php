<?php
// config.php - Conexão com o banco de dados SQLite

$databasePath = __DIR__ . '/db/pdvtarefas.db';

// Define o fuso-horário padrão para evitar divergência na gravação de datas
date_default_timezone_set('America/Sao_Paulo');

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

// Arquivamento automático das tarefas finalizadas no sábado
if (date('N') == 6) { // 6 representa o sábado
    $stmt = $pdo->prepare(
        "UPDATE tarefas SET status = 'Arquivada', updated_at = ? WHERE status = 'Finalizado'"
    );
    $stmt->execute([date('Y-m-d H:i:s')]);
}
?>