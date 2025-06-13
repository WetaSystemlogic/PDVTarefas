<?php
require 'config.php';

// Busca tarefas do banco de dados
function obterTarefasPorStatus($pdo, $status) {
    $stmt = $pdo->prepare("SELECT t.id, t.titulo, t.detalhes, r.nome AS responsavel FROM tarefas t LEFT JOIN responsaveis r ON t.responsavel_id = r.id WHERE status = ? ORDER BY t.id DESC");
    $stmt->execute([$status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$statuses = ['A fazer', 'Fazendo', 'Agendado', 'Aguardando', 'Finalizado'];
$tarefas = [];
foreach ($statuses as $s) {
    $tarefas[$s] = obterTarefasPorStatus($pdo, $s);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDVTarefas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">PDVTarefas</a>
        <div class="d-flex">
            <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#novaTarefaModal">Nova Tarefa</button>
            <button class="btn btn-light">Configurações</button>
        </div>
    </div>
</nav>
<div class="container-fluid mt-4">
    <div class="row" id="kanban-board">
        <?php foreach ($statuses as $status): ?>
        <div class="col-md-2 col-12 mb-3">
            <h5 class="text-center text-white p-2 bg-primary"><?= htmlspecialchars($status) ?></h5>
            <?php foreach ($tarefas[$status] as $tarefa): ?>
            <div class="card mb-2" data-id="<?= $tarefa['id'] ?>" data-bs-toggle="modal" data-bs-target="#detalhesModal" onclick="carregarDetalhes(<?= $tarefa['id'] ?>)">
                <div class="card-body p-2">
                    <h6 class="card-title mb-1"><?= htmlspecialchars($tarefa['titulo']) ?></h6>
                    <p class="mb-1 small"><?= htmlspecialchars($tarefa['detalhes']) ?></p>
                    <p class="mb-0"><span class="badge bg-secondary">Responsável: <?= htmlspecialchars($tarefa['responsavel'] ?? 'N/A') ?></span></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Nova Tarefa -->
<div class="modal fade" id="novaTarefaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="salvar_tarefa.php">
      <div class="modal-header">
        <h5 class="modal-title">Nova Tarefa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Título</label>
          <input type="text" class="form-control" name="titulo" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Detalhes</label>
          <textarea class="form-control" name="detalhes"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Responsável</label>
          <select class="form-select" name="responsavel_id">
            <option value="">Selecione...</option>
            <?php
            $resp = $pdo->query('SELECT id, nome FROM responsaveis')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($resp as $r) {
                echo '<option value="'.$r['id'].'">'.htmlspecialchars($r['nome']).'</option>';
            }
            ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Cliente</label>
          <select class="form-select" name="cliente_id">
            <option value="">Selecione...</option>
            <?php
            $cli = $pdo->query('SELECT id, nome FROM clientes')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cli as $c) {
                echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['nome']).'</option>';
            }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="detalhesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="detalhesConteudo">
      <!-- Conteúdo preenchido via Ajax -->
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
