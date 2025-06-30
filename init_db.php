<?php
require 'config.php';

// Cria tabelas se não existirem
$queries = [
    "CREATE TABLE IF NOT EXISTS responsaveis (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL
    );",
    "CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cnpj TEXT UNIQUE,
        nome TEXT NOT NULL
    );",
    "CREATE TABLE IF NOT EXISTS tarefas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        detalhes TEXT,
        responsavel_id INTEGER,
        cliente_id INTEGER,
        tipo_atendimento TEXT DEFAULT 'Remoto',
        status TEXT NOT NULL DEFAULT 'A fazer',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );",
    "CREATE TABLE IF NOT EXISTS subtarefas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tarefa_id INTEGER NOT NULL,
        descricao TEXT NOT NULL,
        concluida INTEGER NOT NULL DEFAULT 0
    );",
    "CREATE TABLE IF NOT EXISTS comentarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tarefa_id INTEGER NOT NULL,
        texto TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );"
];

foreach ($queries as $query) {
    $pdo->exec($query);
}

// Adiciona coluna tipo_atendimento se não existir
$cols = $pdo->query("PRAGMA table_info(tarefas)")->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('tipo_atendimento', $cols)) {
    $pdo->exec("ALTER TABLE tarefas ADD COLUMN tipo_atendimento TEXT DEFAULT 'Remoto'");
}

echo "Banco de dados inicializado com sucesso.\n";
?>