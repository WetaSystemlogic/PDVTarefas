<?php
require 'auth.php';

// Busca tarefas do banco de dados
function obterTarefasPorStatus(
  $pdo,
  $status,
  $cadastroDe = null,
  $cadastroAte = null,
  $modificacaoDe = null,
  $modificacaoAte = null
) {
  $sql =
      "SELECT t.id, t.titulo, t.detalhes, t.created_at, t.status, t.tipo_atendimento, " .
      "r.nome AS responsavel, c.nome AS cliente, " .
      "(SELECT COUNT(*) FROM comentarios com " .
      " LEFT JOIN comentarios_lidos l ON com.id = l.comentario_id AND l.usuario_id = :uid " .
      " WHERE com.tarefa_id = t.id AND com.usuario_id != :uid AND l.comentario_id IS NULL) AS nao_lidos " .
      "FROM tarefas t " .
      "LEFT JOIN responsaveis r ON t.responsavel_id = r.id " .
      "LEFT JOIN clientes c ON t.cliente_id = c.id " .
      "WHERE t.status = :status"; // Alterado de ? para :status
  
  $params = [
      ':status' => $status,
      ':uid' => $_SESSION['usuario_id']
  ];

  if ($cadastroDe) {
    $sql .= " AND date(t.created_at) >= :cadastroDe";
    $params[':cadastroDe'] = $cadastroDe;
  }
  if ($cadastroAte) {
    $sql .= " AND date(t.created_at) <= :cadastroAte";
    $params[':cadastroAte'] = $cadastroAte;
  }
  if ($modificacaoDe) {
    $sql .= " AND date(t.updated_at) >= :modificacaoDe";
    $params[':modificacaoDe'] = $modificacaoDe;
  }
  if ($modificacaoAte) {
    $sql .= " AND date(t.updated_at) <= :modificacaoAte";
    $params[':modificacaoAte'] = $modificacaoAte;
  }

  $sql .= " ORDER BY t.id DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params); // Executa com o array de parâmetros nomeados
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$defaultStatuses = ['A fazer', 'Fazendo', 'Agendado', 'Aguardando', 'Finalizado'];
$stmt = $pdo->query("SELECT DISTINCT status FROM tarefas");
$dbStatuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
$statuses = array_unique(array_merge($defaultStatuses, $dbStatuses));
$statuses = array_values(array_diff($statuses, ['Arquivada']));
$tarefas = [];
// Filtros de data
$dataCadastroDe = $_GET['data_cadastro_de'] ?? null;
$dataCadastroAte = $_GET['data_cadastro_ate'] ?? null;
$dataModificacaoDe = $_GET['data_modificacao_de'] ?? null;
$dataModificacaoAte = $_GET['data_modificacao_ate'] ?? null;
foreach ($statuses as $s) {
  $tarefas[$s] = obterTarefasPorStatus(
      $pdo,
      $s,
      $dataCadastroDe,
      $dataCadastroAte,
      $dataModificacaoDe,
      $dataModificacaoAte
  );
}
$arquivadas = obterTarefasPorStatus($pdo, 'Arquivada');

$responsaveis = $pdo->query('SELECT id, nome FROM responsaveis')->fetchAll(PDO::FETCH_ASSOC);
$clientes = $pdo->query('SELECT id, cnpj, nome FROM clientes')->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query('SELECT id, nome FROM usuarios')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDVTarefas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.0">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">PDVTarefas</a>
        <div class="d-flex">
            <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#novaTarefaModal">Nova Tarefa</button>
            <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#cadastroModal">Cadastro</button>
            <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#arquivadasModal">Arquivadas</button>
            <a class="btn btn-danger" href="logout.php">Sair</a>
        </div>
    </div>
    </nav>
<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
          <form id="filtrosForm" class="row gy-2 gx-2 align-items-end" method="GET" action="index.php">
            <div class="col-auto">
                    <label class="form-label" for="dataCadastroDe">Cadastro de</label>
                    <input type="date" id="dataCadastroDe" name="data_cadastro_de" class="form-control" value="<?= htmlspecialchars($dataCadastroDe ?? '') ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="dataCadastroAte">Cadastro até</label>
                    <input type="date" id="dataCadastroAte" name="data_cadastro_ate" class="form-control" value="<?= htmlspecialchars($dataCadastroAte ?? '') ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="dataModificacaoDe">Modificação de</label>
                    <input type="date" id="dataModificacaoDe" name="data_modificacao_de" class="form-control" value="<?= htmlspecialchars($dataModificacaoDe ?? '') ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="dataModificacaoAte">Modificação até</label>
                    <input type="date" id="dataModificacaoAte" name="data_modificacao_ate" class="form-control" value="<?= htmlspecialchars($dataModificacaoAte ?? '') ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
                <div class="col-auto">
                    <a href="index.php" class="btn btn-secondary">Mostrar Todos</a>
                </div>
            </form>
        </div>
    </div>
    <div class="row" id="kanban-board">
    <?php foreach ($statuses as $status): ?>
        <div class="col-md-2 col-12 mb-3">
            <h5 class="text-center text-white p-2 bg-primary"><?= htmlspecialchars($status) ?></h5>
            <div class="tarefa-col" data-status="<?= htmlspecialchars($status) ?>">
            <?php foreach ($tarefas[$status] as $tarefa): ?>
              <?php
                    if ($tarefa['status'] === 'Finalizado') {
                        $tempo = 'Finalizado';
                        $badge = 'primary';
                    } else {
                        $diff = (new DateTime($tarefa['created_at']))->diff(new DateTime())->days;
                        if ($diff == 0) {
                            $tempo = 'Normal';
                            $badge = 'success';
                        } elseif ($diff == 1) {
                            $tempo = 'Atrasada';
                            $badge = 'warning';
                        } elseif ($diff == 2) {
                            $tempo = 'Muito atrasada';
                            $badge = 'danger';
                        } elseif ($diff > 5) {
                            $tempo = 'Urgente muito atrasada';
                            $badge = 'dark';
                        } else {
                            $tempo = 'Muito atrasada';
                            $badge = 'danger';
                        }
                    }

                    $detalhesPreview = strlen($tarefa['detalhes']) > 200
                        ? substr($tarefa['detalhes'],0,200) . '...'
                        : $tarefa['detalhes'];
                ?>
                <div class="card mb-2 tarefa-card position-relative" data-id="<?= $tarefa['id'] ?>">
                    <div class="card-body p-2 pb-4">
                    <div class="position-absolute top-0 end-0 m-1 text-danger icon-notificacao">
                            <i class="bi bi-bell"></i>
                            <?php if ($tarefa['nao_lidos'] > 0): ?>
                                <span class="badge bg-danger">
                                    <?= $tarefa['nao_lidos'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h6 class="card-title mb-1 pe-4"><?= htmlspecialchars($tarefa['titulo']) ?></h6>
                        <p class="mb-1 small"><?= htmlspecialchars($detalhesPreview) ?></p>
                        <p class="mb-0"><span class="badge bg-info text-dark badge-cliente">Cliente: <?= htmlspecialchars($tarefa['cliente'] ?? '...') ?></span></p>
                        <p class="mb-0"><span class="badge bg-secondary">Responsável: <?= htmlspecialchars($tarefa['responsavel'] ?? '...') ?></span></p>
                        <p class="mb-0 mt-1"><span class="badge bg-<?= $badge ?>"><?= $tempo ?></span></p>
                        <p class="mb-0"><span class="badge badge-atendimento">
                            <?= htmlspecialchars($tarefa['tipo_atendimento']) ?>
                        </span></p>
                        <div class="card-actions d-flex gap-1">
                            <?php if ($tarefa['status'] === 'Finalizado'): ?>
                                <button class="btn btn-light btn-sm btn-arquivar" title="Arquivar"><i class="bi bi-archive"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-light btn-sm btn-duplicar" title="Duplicar"><i class="bi bi-files"></i></button>
                            <?php if ($tarefa['status'] !== 'Finalizado'): ?>
                                <button class="btn btn-light btn-sm btn-finalizar" title="Finalizar"><i class="bi bi-check-circle"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Nova Tarefa -->
<div class="modal fade" id="novaTarefaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="novaTarefaForm" class="modal-content" method="POST" action="salvar_tarefa.php">
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
            foreach ($responsaveis as $r) {
                echo '<option value="'.$r['id'].'">'.htmlspecialchars($r['nome']).'</option>';
            }
            ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Cliente</label>
          <input type="hidden" name="cliente_id" id="cliente_id">
          <div class="dropdown" id="clienteDropdown">
            <button class="form-select text-start" type="button" id="clienteDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">Selecione...</button>
            <ul class="dropdown-menu w-100" id="clienteDropdownMenu" aria-labelledby="clienteDropdownBtn">
              <li class="px-3"><input type="text" class="form-control" id="clienteFiltro" placeholder="Buscar..."></li>
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
            <option value="Remoto">Remoto</option>
            <option value="Presencial">Presencial</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Data e Hora de Criação</label>
          <input type="datetime-local" class="form-control" name="created_at" value="<?= date('Y-m-d\TH:i') ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
    </div>
</div>

<!-- Modal Cadastro -->
<div class="modal fade" id="cadastroModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
      <button class="btn btn-primary me-2" data-bs-target="#listaResponsavelModal" data-bs-toggle="modal" data-bs-dismiss="modal">Responsável</button>
        <button class="btn btn-primary me-2" data-bs-target="#listaClienteModal" data-bs-toggle="modal" data-bs-dismiss="modal">Cliente</button>
        <button class="btn btn-primary" data-bs-target="#listaUsuarioModal" data-bs-toggle="modal" data-bs-dismiss="modal">Usuário</button>
      </div>
    </div>
  </div>
  </div>

<!-- Lista Responsáveis -->
<div class="modal fade" id="listaResponsavelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Responsáveis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-end mb-2">
          <button class="btn btn-primary" id="btnNovoResponsavel">Novo</button>
        </div>
        <table class="table table-striped">
          <thead>
            <tr><th>Nome</th><th>Ação</th></tr>
          </thead>
          <tbody>
            <?php foreach ($responsaveis as $r): ?>
            <tr data-id="<?= $r['id'] ?>" data-nome="<?= htmlspecialchars($r['nome']) ?>">
              <td><?= htmlspecialchars($r['nome']) ?></td>
              <td>
                <button class="btn btn-sm btn-secondary btn-editar-resp">Editar</button>
                <button class="btn btn-sm btn-danger btn-excluir-resp">Excluir</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div>

<!-- Lista Clientes -->
<div class="modal fade" id="listaClienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Clientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-end mb-2">
          <input type="text" class="form-control w-50 me-2" id="clienteBusca" placeholder="Filtrar...">
          <button class="btn btn-primary" id="btnNovoCliente">Novo</button>
        </div>
        <table class="table table-striped">
          <thead>
            <tr><th>CNPJ</th><th>Nome</th><th>Ação</th></tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $c): ?>
            <tr data-id="<?= $c['id'] ?>" data-cnpj="<?= htmlspecialchars($c['cnpj']) ?>" data-nome="<?= htmlspecialchars($c['nome']) ?>">
              <td><?= htmlspecialchars($c['cnpj']) ?></td>
              <td><?= htmlspecialchars($c['nome']) ?></td>
              <td>
                <button class="btn btn-sm btn-secondary btn-editar-cliente">Editar</button>
                <button class="btn btn-sm btn-danger btn-excluir-cliente">Excluir</button>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <nav>
          <ul class="pagination justify-content-center" id="paginacaoClientes">
            <li class="page-item"><a href="#" class="page-link" id="btnPrevCliente">Anterior</a></li>
            <li class="page-item disabled"><span class="page-link" id="paginaAtual">1/1</span></li>
            <li class="page-item"><a href="#" class="page-link" id="btnNextCliente">Próxima</a></li>
          </ul>
        </nav>
      </div>
    </div>
    </div>
  </div>

<!-- Lista Usuários -->
<div class="modal fade" id="listaUsuarioModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Usuários</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-end mb-2">
          <button class="btn btn-primary" id="btnNovoUsuario">Novo</button>
        </div>
        <table class="table table-striped">
          <thead>
            <tr><th>Nome</th><th>Ação</th></tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr data-id="<?= $u['id'] ?>" data-nome="<?= htmlspecialchars($u['nome']) ?>">
              <td><?= htmlspecialchars($u['nome']) ?></td>
              <td>
                <button class="btn btn-sm btn-secondary btn-editar-usuario">Editar</button>
                <button class="btn btn-sm btn-danger btn-excluir-usuario">Excluir</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Arquivadas -->
<div class="modal fade" id="arquivadasModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tarefas Arquivadas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if ($arquivadas): ?>
          <ul class="list-group">
            <?php foreach ($arquivadas as $a): ?>
              <li class="list-group-item d-flex justify-content-between">
                <?= htmlspecialchars($a['titulo']) ?>
                <small class="text-muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Nenhuma tarefa arquivada.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Responsável -->
<div class="modal fade" id="responsavelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formResponsavel">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Responsável</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="respAlert"></div>
        <input type="hidden" name="id">
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
  </div>

<!-- Modal Cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formCliente">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="cliAlert"></div>
        <input type="hidden" name="id">
        <div class="mb-3">
          <label class="form-label">CNPJ</label>
          <input type="text" class="form-control" name="cnpj" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
  </div>

<!-- Modal Usuário -->
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formUsuario">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="userAlert"></div>
        <input type="hidden" name="id">
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" required>
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
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
var clientesData = <?php echo json_encode($clientes); ?>;
</script>
<script src="assets/js/app.js?v=1.0.6"></script>
</body>
</html>