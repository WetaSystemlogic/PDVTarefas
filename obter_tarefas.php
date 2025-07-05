<?php
require 'auth.php';

function obterTarefasPorStatus($pdo, $status, $cadastroDe = null, $cadastroAte = null, $modificacaoDe = null, $modificacaoAte = null) {
    $sql = "SELECT t.id, t.titulo, t.detalhes, t.created_at, t.status, t.tipo_atendimento, " .
           "r.nome AS responsavel, c.nome AS cliente, " .
           "(SELECT COUNT(*) FROM comentarios com " .
           " LEFT JOIN comentarios_lidos l ON com.id = l.comentario_id AND l.usuario_id = :uid " .
           " WHERE com.tarefa_id = t.id AND com.usuario_id != :uid AND l.comentario_id IS NULL) AS nao_lidos " .
           "FROM tarefas t " .
           "LEFT JOIN responsaveis r ON t.responsavel_id = r.id " .
           "LEFT JOIN clientes c ON t.cliente_id = c.id " .
           "WHERE t.status = ?";
    $params = [$status];
    if ($cadastroDe) {
        $sql .= " AND date(t.created_at) >= ?";
        $params[] = $cadastroDe;
    }
    if ($cadastroAte) {
        $sql .= " AND date(t.created_at) <= ?";
        $params[] = $cadastroAte;
    }
    if ($modificacaoDe) {
        $sql .= " AND date(t.updated_at) >= ?";
        $params[] = $modificacaoDe;
    }
    if ($modificacaoAte) {
        $sql .= " AND date(t.updated_at) <= ?";
        $params[] = $modificacaoAte;
    }
    $sql .= " ORDER BY t.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':uid', $_SESSION['usuario_id']);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$statuses = ['A fazer', 'Fazendo', 'Agendado', 'Aguardando', 'Finalizado'];
$dataCadastroDe = $_GET['data_cadastro_de'] ?? null;
$dataCadastroAte = $_GET['data_cadastro_ate'] ?? null;
$dataModificacaoDe = $_GET['data_modificacao_de'] ?? null;
$dataModificacaoAte = $_GET['data_modificacao_ate'] ?? null;

$result = [];
foreach ($statuses as $status) {
    $tarefas = obterTarefasPorStatus(
        $pdo,
        $status,
        $dataCadastroDe,
        $dataCadastroAte,
        $dataModificacaoDe,
        $dataModificacaoAte
    );

    ob_start();
    foreach ($tarefas as $tarefa) {
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
        $detalhesPreview = mb_strlen($tarefa['detalhes']) > 200
            ? mb_substr($tarefa['detalhes'], 0, 200) . '...'
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
                <p class="mb-0"><span class="badge bg-info text-dark badge-cliente">Cliente: <?= htmlspecialchars($tarefa['cliente'] ?? 'N/A') ?></span></p>
                <p class="mb-0"><span class="badge bg-secondary">Responsável: <?= htmlspecialchars($tarefa['responsavel'] ?? 'N/A') ?></span></p>
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
        <?php
    }
    $result[$status] = ob_get_clean();
}

header('Content-Type: application/json');
echo json_encode($result);