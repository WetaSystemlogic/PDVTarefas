function carregarDetalhes(id) {
    $.get('detalhes_tarefa.php', {id: id}, function(data) {
        $('#detalhesConteudo').html(data);
    });
}

$(function() {
    $('#formResponsavel').on('submit', function(e) {
        e.preventDefault();
        $.post('salvar_responsavel.php', $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#responsavelModal').modal('hide');
                location.reload();
            } else {
                $('#respAlert').html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+resp.message+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        }, 'json');
    });

    $('#formCliente').on('submit', function(e) {
        e.preventDefault();
        $.post('salvar_cliente.php', $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#clienteModal').modal('hide');
                location.reload();
            } else {
                $('#cliAlert').html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+resp.message+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        }, 'json');
    });
});
