function carregarDetalhes(id) {
    $.get('detalhes_tarefa.php', {id: id}, function(data) {
        $('#detalhesConteudo').html(data);
        if (typeof Quill !== 'undefined') {
            window.quill = new Quill('#comentarioEditor', {theme: 'snow'});
        }
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

    // Delegação para formulário de atualização de status
    $(document).on('submit', '#formStatus', function(e) {
        e.preventDefault();
        $.post('atualizar_status.php', $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#detalhesModal').modal('hide');
                location.reload();
            }
        }, 'json');
    });

    // Atualização de detalhes
    $(document).on('submit', '#formTarefaDetalhes', function(e){
        e.preventDefault();
        $.post('atualizar_tarefa.php', $(this).serialize(), function(resp){
            if(resp.success){
                $('#detalhesModal').modal('hide');
                location.reload();
            }
        }, 'json');
    });

    // Salvar comentário
    $(document).on('click', '#btnSalvarComentario', function(){
        var id = $('#formTarefaDetalhes input[name=id]').val();
        var texto = window.quill.root.innerHTML;
        $.post('salvar_comentario.php', {tarefa_id: id, texto: texto}, function(resp){
            if(resp.success){
                carregarDetalhes(id);
            }
        }, 'json');
    });

    // Excluir tarefa
    $(document).on('click', '#btnExcluirTarefa', function(){
        var id = $('#formTarefaDetalhes input[name=id]').val();
        var titulo = $('#detalhesTitulo').val();
        Swal.fire({
            title: 'Excluir "'+titulo+'"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não'
        }).then(function(result){
            if(result.isConfirmed){
                $.post('excluir_tarefa.php', {id:id}, function(resp){
                    if(resp.success){
                        $('#detalhesModal').modal('hide');
                        location.reload();
                    }
                }, 'json');
            }
        });
    });

    // Drag and drop das tarefas
    var isDragging = false;
    $('.tarefa-col').sortable({
        connectWith: '.tarefa-col',
        placeholder: 'card-placeholder',
        forcePlaceholderSize: true,
        start: function() {
            isDragging = true;
        },
        stop: function() {
            // pequeno delay para diferenciar clique de arraste
            setTimeout(function(){ isDragging = false; }, 100);
        },
        receive: function(event, ui) {
            var id = ui.item.data('id');
            var status = $(this).data('status');
            $.post('atualizar_status.php', {id: id, status: status});
        }
    }).disableSelection();

    // Impede abertura do modal quando o card está sendo arrastado
    $(document).on('click', '.tarefa-card', function(e){
        if(isDragging){
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    if(typeof clientesData !== 'undefined'){
        var clientesMap = clientesData.map(function(c){
            return {label: c.nome + ' (' + c.cnpj + ')', value: c.id};
        });
        $('#clienteBusca').autocomplete({
            source: clientesMap,
            minLength: 0,
            select: function(event, ui){
                $('#cliente_id').val(ui.item.value);
            },
            change: function(event, ui){
                if(!ui.item){
                    $('#cliente_id').val('');
                }
            }
        }).on('focus', function(){
            $(this).autocomplete('search', $(this).val());
        });
    }
});