<?php
// config.php - Conexão com o banco de dados SQLite

session_start();

$databasePath = __DIR__ . '/db/pdvtarefas.db';

// Define o fuso-horário padrão para evitar divergência na gravação de datas
date_default_timezone_set('America/Sao_Paulo');

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

// Garante que estruturas adicionadas em versoes anteriores existam
$cols = $pdo->query("PRAGMA table_info(tarefas)")->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('tipo_atendimento', $cols)) {
    $pdo->exec("ALTER TABLE tarefas ADD COLUMN tipo_atendimento TEXT DEFAULT 'Remoto'");
}

$colsComentarios = $pdo->query("PRAGMA table_info(comentarios)")->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('imagem', $colsComentarios)) {
    $pdo->exec("ALTER TABLE comentarios ADD COLUMN imagem TEXT");
}
if (!in_array('usuario_id', $colsComentarios)) {
    $pdo->exec("ALTER TABLE comentarios ADD COLUMN usuario_id INTEGER");
}
if (!in_array('lido', $colsComentarios)) {
    $pdo->exec("ALTER TABLE comentarios ADD COLUMN lido INTEGER DEFAULT 0");
}

$pdo->exec("CREATE TABLE IF NOT EXISTS comentarios_lidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    comentario_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL
);");

if (!isset($_SESSION['usuario_id']) && !empty($_COOKIE['manter_conectado'])) {
    $uid = (int)$_COOKIE['manter_conectado'];
    $stmt = $pdo->prepare('SELECT nome FROM usuarios WHERE id = ?');
    if ($stmt->execute([$uid])) {
        $nome = $stmt->fetchColumn();
        if ($nome) {
            $_SESSION['usuario_id'] = $uid;
            $_SESSION['usuario_nome'] = $nome;
        }
    }
}

// Arquivamento automático das tarefas finalizadas no sábado
if (date('N') == 6) { // 6 representa o sábado
    $stmt = $pdo->prepare(
        "UPDATE tarefas SET status = 'Arquivada', updated_at = ? WHERE status = 'Finalizado'"
    );
    $stmt->execute([date('Y-m-d H:i:s')]);
}