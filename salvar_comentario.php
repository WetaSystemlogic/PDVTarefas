<?php
require 'auth.php';

$tarefa_id = $_POST['tarefa_id'] ?? 0;
$texto = $_POST['texto'] ?? '';
$imagemPath = null;

if (!empty($_FILES['imagem']['name'])) {
    $dir = 'assets/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $nome = uniqid('img_') . '.' . $ext;
    $destino = $dir . '/' . $nome;
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
        $imagemPath = $destino;
    }
}

if ($tarefa_id && $texto !== '') {
    $userId = $_SESSION['usuario_id'];
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO comentarios (tarefa_id, usuario_id, texto, imagem, created_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$tarefa_id, $userId, $texto, $imagemPath, $now]);
    $comentarioId = $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO comentarios_lidos (comentario_id, usuario_id) VALUES (?, ?)')->execute([$comentarioId, $userId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
