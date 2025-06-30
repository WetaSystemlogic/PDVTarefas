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
$statuses = ['A fazer','Fazendo','Agendado','Aguardando','Finalizado'];
$responsaveis = $pdo->query('SELECT id, nome FROM responsaveis')->fetchAll(PDO::FETCH_ASSOC);
$clientes = $pdo->query('SELECT id, cnpj, nome FROM clientes')->fetchAll(PDO::FETCH_ASSOC);
$com = $pdo->prepare('SELECT texto, created_at FROM comentarios WHERE tarefa_id = ? ORDER BY id DESC');
$com->execute([$id]);
$comentarios = $com->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="modal-header">
  <h5 class="modal-title">Detalhes da Tarefa</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
  <form id="formTarefaDetalhes">
    <input type="hidden" name="id" value="<?= $tarefa['id'] ?>">
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" class="form-control" id="detalhesTitulo" name="titulo" value="<?= htmlspecialchars($tarefa['titulo']) ?>" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Detalhes</label>
      <textarea class="form-control" name="detalhes" rows="3"><?= htmlspecialchars($tarefa['detalhes']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Responsável</label>
      <select class="form-select" name="responsavel_id">
        <option value="">Selecione...</option>
        <?php foreach ($responsaveis as $r): ?>
          <option value="<?= $r['id'] ?>" <?= $tarefa['responsavel_id'] == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Cliente</label>
      <input type="hidden" name="cliente_id" id="det_cliente_id" value="<?= $tarefa['cliente_id'] ?>">
      <div class="dropdown" id="detClienteDropdown">
        <?php
            $clienteNome = 'Selecione...';
            foreach ($clientes as $c) {
                if ($c['id'] == $tarefa['cliente_id']) {
                    $clienteNome = htmlspecialchars($c['nome']);
                    break;
                }
            }
        ?>
        <button class="form-select text-start" type="button" id="detClienteDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
          <?= $clienteNome ?>
        </button>
        <ul class="dropdown-menu w-100" id="detClienteDropdownMenu" aria-labelledby="detClienteDropdownBtn">
          <li class="px-3"><input type="text" class="form-control" id="detClienteFiltro" placeholder="Buscar..."></li>
          <li><hr class="dropdown-divider"></li>
          <?php foreach ($clientes as $c): ?>
          <li><a class="dropdown-item" href="#" data-id="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?> (<?= htmlspecialchars($c['cnpj']) ?>)</a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Tipo de atendimento</label>
      <select class="form-select" name="tipo_atendimento">
        <option value="Remoto" <?= $tarefa['tipo_atendimento'] == 'Remoto' ? 'selected' : '' ?>>Remoto</option>
        <option value="Presencial" <?= $tarefa['tipo_atendimento'] == 'Presencial' ? 'selected' : '' ?>>Presencial</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary mb-3">Salvar Alterações</button>
  </form>

  <p><strong>Criada em:</strong> <?= date('d/m/Y H:i', strtotime($tarefa['created_at'])) ?></p>
  <p><strong>Atualizada em:</strong> <?= date('d/m/Y H:i', strtotime($tarefa['updated_at'])) ?></p>
  <?php if ($subtarefas): ?>
    <h6>Checklist</h6>
    <ul>
      <?php foreach ($subtarefas as $s): ?>
        <li><?= htmlspecialchars($s['descricao']) ?> <?= $s['concluida'] ? '(Ok)' : '' ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <form id="formStatus">
    <input type="hidden" name="id" value="<?= $tarefa['id'] ?>">
    <div class="mb-3">
      <label class="form-label">Situação</label>
      <select class="form-select" name="status">
        <?php foreach ($statuses as $s): ?>
          <option value="<?= $s ?>" <?= $s == $tarefa['status'] ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-secondary">Salvar Situação</button>
  </form>

  <h6 class="mt-3">Comentários</h6>
  <div id="listaComentarios">
    <?php foreach ($comentarios as $c): ?>
      <div class="border p-2 mb-2">
        <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></div>
        <div><?= $c['texto'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <div id="comentarioEditor" style="height:100px;" class="mb-2"></div>
  <button type="button" class="btn btn-success" id="btnSalvarComentario">Salvar Comentário</button>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger me-auto" id="btnExcluirTarefa">Excluir</button>
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
</div>
<script>
$(function(){
    $('#detClienteFiltro').on('keyup', function(){
        var termo = $(this).val().toLowerCase();
        $('#detClienteDropdownMenu a.dropdown-item').each(function(){
            var txt = $(this).text().toLowerCase();
            $(this).toggle(txt.indexOf(termo) !== -1);
        });
    });

    $('#detClienteDropdownMenu').on('click', 'a.dropdown-item', function(e){
        e.preventDefault();
        var nome = $(this).text();
        var id = $(this).data('id');
        $('#detClienteDropdownBtn').text(nome);
        $('#det_cliente_id').val(id);
    });
});
</script>