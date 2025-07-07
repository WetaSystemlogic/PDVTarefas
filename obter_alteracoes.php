<?php
require 'auth.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT a.descricao, u.nome AS usuario, a.created_at FROM alteracoes a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.tarefa_id = ? ORDER BY a.id DESC');
$stmt->execute([$id]);
$alteracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="table table-striped">
  <thead>
    <tr><th>Alteração</th><th>Usuário</th><th>Data/Hora</th></tr>
  </thead>
  <tbody>
  <?php foreach($alteracoes as $alt): ?>
    <tr>
      <td><?= htmlspecialchars($alt['descricao']) ?></td>
      <td><?= htmlspecialchars($alt['usuario']) ?></td>
      <td><?= date('d/m/Y H:i', strtotime($alt['created_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>