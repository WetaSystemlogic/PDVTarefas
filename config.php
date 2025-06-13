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
?>