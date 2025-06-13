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

echo "Banco de dados inicializado com sucesso.\n";
?>