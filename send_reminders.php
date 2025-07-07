<?php
require 'auth.php';
require 'funcoes.php';

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
                $erroEnvio = '';
                $codigo = 0;
                foreach ($whatsappConfig['numbers'] as $num) {
                    $erro = enviarMensagemWhatsApp($num, $msg, $codigo);
                    if ($codigo != 200) {
                        $erroEnvio = $erro ?: 'HTTP ' . $codigo;
                    }
                }
                $ins = $pdo->prepare('INSERT INTO lembretes_enviados (tarefa_id, momento) VALUES (?, ?)');
                $ins->execute([$tarefa['id'], $min]);
                if ($erroEnvio === '') {
                    registrarAlteracao($pdo, $tarefa['id'], $_SESSION['usuario_id'], 'Mensagem de Agendamento enviada com Sucesso');
                } else {
                    registrarAlteracao($pdo, $tarefa['id'], $_SESSION['usuario_id'], 'Erro ao enviar mensagem: ' . $erroEnvio);
                }
            }
        }
    }
}