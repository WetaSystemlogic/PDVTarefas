<?php
function registrarAlteracao(PDO $pdo, $tarefaId, $usuarioId, $descricao){
    $stmt = $pdo->prepare('INSERT INTO alteracoes (tarefa_id, usuario_id, descricao, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$tarefaId, $usuarioId, $descricao, date('Y-m-d H:i:s')]);
}

function enviarMensagemWhatsApp($numero, $mensagem, &$httpCode = null){
    global $whatsappConfig;
    $url = $whatsappConfig['endpoint'] . '/' . $whatsappConfig['sessionId'];
    $payload = json_encode([
        'chatId'      => $numero . '@c.us',
        'contentType' => 'string',
        'content'     => $mensagem
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $whatsappConfig['apiKey']
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return $error;
}
?>