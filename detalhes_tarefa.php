<?php
require 'config.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT t.*, r.nome AS responsavel, c.nome AS cliente FROM tarefas t
    LEFT JOIN responsaveis r ON t.responsavel_id = r.id
    LEFT JOIN clientes c ON t.cliente_id = c.id
    WHERE t.id = ?');
$stmt->execute([$id]);
$tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarefa) {
    echo '<div class="modal-header"><h5 class="modal-title">Tarefa não encontrada</h5></div>';
    exit;
}

$sub = $pdo->prepare('SELECT * FROM subtarefas WHERE tarefa_id = ?');
$sub->execute([$id]);
$subtarefas = $sub->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="modal-header">
  <h5 class="modal-title">Detalhes da Tarefa</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
  <h6><?= htmlspecialchars($tarefa['titulo']) ?></h6>
  <p><?= nl2br(htmlspecialchars($tarefa['detalhes'])) ?></p>
  <p><strong>Responsável:</strong> <?= htmlspecialchars($tarefa['responsavel'] ?? 'N/A') ?></p>
  <p><strong>Cliente:</strong> <?= htmlspecialchars($tarefa['cliente'] ?? 'N/A') ?></p>
  <?php if ($subtarefas): ?>
    <h6>Checklist</h6>
    <ul>
      <?php foreach ($subtarefas as $s): ?>
        <li><?= htmlspecialchars($s['descricao']) ?> <?= $s['concluida'] ? '(Ok)' : '' ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
</div>