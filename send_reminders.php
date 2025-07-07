<?php
require 'config.php';

function enviarMensagemWhatsApp($numero, $mensagem){
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
    curl_close($ch);
}

$intervalos = [30, 20, 10];
$sql = "SELECT t.id, t.titulo, t.detalhes, t.data_hora_agendamento, c.nome AS cliente, t.tipo_atendimento
        FROM tarefas t LEFT JOIN clientes c ON t.cliente_id = c.id
        WHERE t.status = 'Agendado' AND t.data_hora_agendamento IS NOT NULL";
$tarefas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($tarefas as $tarefa) {
    $ag = new DateTime($tarefa['data_hora_agendamento']);
    $diff = floor(($ag->getTimestamp() - time()) / 60);
    foreach ($intervalos as $min) {
        if ($diff == $min) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM lembretes_enviados WHERE tarefa_id = ? AND momento = ?');
            $stmt->execute([$tarefa['id'], $min]);
            if (!$stmt->fetchColumn()) {
                $msg = "*{$tarefa['titulo']}*\n*{$tarefa['detalhes']}*\nPassando para lembrar do agendamento da tarefa para *{$tarefa['data_hora_agendamento']}*, para o cliente *{$tarefa['cliente']}*, o atendimento vai ser *{$tarefa['tipo_atendimento']}*.";
                foreach ($whatsappConfig['numbers'] as $num) {
                    enviarMensagemWhatsApp($num, $msg);
                }
                $ins = $pdo->prepare('INSERT INTO lembretes_enviados (tarefa_id, momento) VALUES (?, ?)');
                $ins->execute([$tarefa['id'], $min]);
            }
        }
    }
}
