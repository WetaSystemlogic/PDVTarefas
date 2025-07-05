<?php
require 'auth.php';
$id = $_POST['id'] ?? 0;
if($id){
    $userId = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare('SELECT id FROM comentarios WHERE tarefa_id = ?');
    $stmt->execute([$id]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $ins = $pdo->prepare('INSERT OR IGNORE INTO comentarios_lidos (comentario_id, usuario_id) VALUES (?, ?)');
    foreach($comentarios as $cid){
        $ins->execute([$cid, $userId]);
    }
}
echo json_encode(['success'=>true]);
?>