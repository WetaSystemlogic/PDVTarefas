<?php
require 'auth.php';
require 'funcoes.php';

$id = $_POST['id'] ?? 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $pdo->prepare("SELECT t.titulo, t.detalhes, t.data_hora_agendamento, c.nome AS cliente, t.tipo_atendimento FROM tarefas t LEFT JOIN clientes c ON t.cliente_id = c.id WHERE t.id = ?");
$stmt->execute([$id]);
$tarefa = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tarefa) {
    echo json_encode(['success' => false, 'message' => 'Tarefa não encontrada']);
    exit;
}

$dataFormatada = $tarefa['data_hora_agendamento']
    ? date('d/m/Y H:i', strtotime($tarefa['data_hora_agendamento']))
    : '';
$msg = "*{$tarefa['titulo']}*\n*{$tarefa['detalhes']}*\nPassando para lembrar do agendamento da tarefa para *{$dataFormatada}*, para o cliente *{$tarefa['cliente']}*, o atendimento vai ser *{$tarefa['tipo_atendimento']}*.";

$erroEnvio = '';
$codigo = 0;
foreach ($whatsappConfig['numbers'] as $num) {
    $erro = enviarMensagemWhatsApp($num, $msg, $codigo);
    if ($codigo != 200) {
        $erroEnvio = $erro ?: 'HTTP ' . $codigo;
    }
}

if ($erroEnvio === '') {
    registrarAlteracao($pdo, $id, $_SESSION['usuario_id'], 'Mensagem de Agendamento enviada com Sucesso');
    echo json_encode(['success' => true]);
} else {
    registrarAlteracao($pdo, $id, $_SESSION['usuario_id'], 'Erro ao enviar mensagem: ' . $erroEnvio);
    echo json_encode(['success' => false, 'message' => $erroEnvio]);
}
