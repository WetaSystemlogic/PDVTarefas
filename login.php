<?php
require 'config.php';

$usuarios = $pdo->query('SELECT id, nome FROM usuarios ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_POST['usuario_id'] ?? '';
    if ($uid) {
        $stmt = $pdo->prepare('SELECT nome FROM usuarios WHERE id = ?');
        $stmt->execute([$uid]);
        $nome = $stmt->fetchColumn();
        if ($nome) {
            $_SESSION['usuario_id'] = $uid;
            $_SESSION['usuario_nome'] = $nome;
            if (!empty($_POST['manter'])) {
                setcookie('manter_conectado', $uid, time()+60*60*24*30, '/');
            } else {
                setcookie('manter_conectado', '', time()-3600, '/');
            }
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
<div class="card p-4" style="min-width:300px;">
    <h5 class="mb-3">Escolha o Usuário</h5>
    <form method="POST">
        <div class="mb-3">
            <select name="usuario_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="manter" id="manter">
            <label class="form-check-label" for="manter">Manter Conectado</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
</div>
</body>
</html>
