<?php
require 'config.php';
$id = $_POST['id'] ?? 0;
if($id){
    $stmt = $pdo->prepare('UPDATE comentarios SET lido = 1 WHERE tarefa_id = ?');
    $stmt->execute([$id]);
}
echo json_encode(['success'=>true]);
?>
