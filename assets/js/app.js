function carregarDetalhes(id) {
    $.get('detalhes_tarefa.php', {id: id}, function(data) {
        $('#detalhesConteudo').html(data);
    });
}