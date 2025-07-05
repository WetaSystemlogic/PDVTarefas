var detalhesRequest;
function carregarDetalhes(id) {
    // Cancela requisição anterior, se houver
    if(detalhesRequest && detalhesRequest.readyState !== 4){
        detalhesRequest.abort();
    }
    // Exibe placeholder enquanto os dados são carregados
    $('#detalhesConteudo').html(
        '<div class="modal-body p-3 text-center">' +
        '<div class="spinner-border" role="status"></div>' +
        '</div>'
    );
    detalhesRequest = $.get('detalhes_tarefa.php', {id: id}, function(data) {
        $('#detalhesConteudo').html(data);
        if (typeof Quill !== 'undefined') {
            window.quill = new Quill('#comentarioEditor', {theme: 'snow'});
        }
        $.post('marcar_comentarios_lidos.php', {id:id}, function(){
            atualizarKanban();
        });
    });
}

function atualizarKanban(callback){
    var dados = '';
    if($('#filtrosForm').length){
        dados = $('#filtrosForm').serialize();
    }
    $.get('obter_tarefas.php', dados, function(resp){
        if(resp.error === 'not_authenticated'){
            window.location = 'login.php';
            return;
        }
        for(var status in resp){
            $('.tarefa-col[data-status="'+status+'"]').html(resp[status]);
        }
        if(typeof callback === 'function') callback();
    }, 'json').fail(function(){
        window.location = 'login.php';
    });
}

$(function() {
    setInterval(atualizarKanban, 5000); /*5000=Para Atualizar em 5 segunos */

    $('#novaTarefaForm').on('submit', function(e){
        e.preventDefault();
        $.post('salvar_tarefa.php', $(this).serialize(), function(){
            $('#novaTarefaModal').modal('hide');
            atualizarKanban();
        }, 'json');
    });

    $('#formResponsavel').on('submit', function(e) {
        e.preventDefault();
        var id = $(this).find('input[name=id]').val();
        var url = id ? 'atualizar_responsavel.php' : 'salvar_responsavel.php';
        $.post(url, $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#responsavelModal').modal('hide');
                atualizarKanban();
            } else {
                $('#respAlert').html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+resp.message+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        }, 'json');
    });

    $('#formCliente').on('submit', function(e) {
        e.preventDefault();
        var id = $(this).find('input[name=id]').val();
        var url = id ? 'atualizar_cliente.php' : 'salvar_cliente.php';
        $.post(url, $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#clienteModal').modal('hide');
                atualizarKanban();
            } else {
                $('#cliAlert').html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+resp.message+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        }, 'json');
    });

    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();
        var id = $(this).find('input[name=id]').val();
        var url = id ? 'atualizar_usuario.php' : 'salvar_usuario.php';
        $.post(url, $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#usuarioModal').modal('hide');
                location.reload();
            } else {
                $('#userAlert').html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+resp.message+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        }, 'json');
    });

    // Delegação para formulário de atualização de status
    $(document).on('submit', '#formStatus', function(e) {
        e.preventDefault();
        $.post('atualizar_status.php', $(this).serialize(), function(resp) {
            if (resp.success) {
                $('#detalhesModal').modal('hide');
                atualizarKanban();
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
        var formData = new FormData();
        formData.append('tarefa_id', id);
        formData.append('texto', texto);
        var file = $('#comentarioImagem')[0].files[0];
        if(file){
            formData.append('imagem', file);
        }
        $.ajax({
            url: 'salvar_comentario.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp){
                if(resp.success){
                    carregarDetalhes(id);
                }
            }
        });
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
                        atualizarKanban();
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

    // Abre detalhes ao clicar no card, exceto durante arraste ou em botoes
    $(document).on('click', '.tarefa-card', function(e){
        if(isDragging){
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }
        if($(e.target).closest('.card-actions').length){
            return;
        }
        var id = $(this).data('id');
        carregarDetalhes(id);
        $('#detalhesModal').modal('show');
    });

    // Botoes de acao nos cards
    $(document).on('click', '.btn-duplicar', function(e){
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).closest('.tarefa-card').data('id');
        $.post('duplicar_tarefa.php', {id: id}, function(resp){
            if(resp.success){
                atualizarKanban(function(){
                    carregarDetalhes(resp.id);
                    $('#detalhesModal').modal('show');
                });
            }
        }, 'json');
    });

    $(document).on('click', '.btn-finalizar', function(e){
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).closest('.tarefa-card').data('id');
        $.post('atualizar_status.php', {id: id, status: 'Finalizado'}, function(resp){
            if(resp.success){
                atualizarKanban();
            }
        }, 'json');
    });

    $(document).on('click', '.btn-arquivar', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        var id = $(this).closest('.tarefa-card').data('id');
        $.post('atualizar_status.php', {id: id, status: 'Arquivada'}, function(resp){
            if(resp.success){
                atualizarKanban();
            }
        }, 'json');
    });

    // Responsáveis
    $(document).on('click', '#btnNovoResponsavel', function(){
        $('#formResponsavel')[0].reset();
        $('#formResponsavel input[name=id]').val('');
        $('#responsavelModal .modal-title').text('Cadastrar Responsável');
        $('#listaResponsavelModal').modal('hide');
        $('#responsavelModal').modal('show');
    });

    $(document).on('click', '.btn-editar-resp', function(){
        var tr = $(this).closest('tr');
        $('#formResponsavel input[name=id]').val(tr.data('id'));
        $('#formResponsavel input[name=nome]').val(tr.data('nome'));
        $('#responsavelModal .modal-title').text('Editar Responsável');
        $('#listaResponsavelModal').modal('hide');
        $('#responsavelModal').modal('show');
    });

    $(document).on('click', '.btn-excluir-resp', function(){
        var tr = $(this).closest('tr');
        var id = tr.data('id');
        var nome = tr.data('nome');
        Swal.fire({
            title: 'Excluir "'+nome+'"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não'
        }).then(function(res){
            if(res.isConfirmed){
                $.post('excluir_responsavel.php', {id:id}, function(resp){
                    if(resp.success){
                        atualizarKanban();
                    }
                }, 'json');
            }
        });
    });

    // Clientes
    $(document).on('click', '#btnNovoCliente', function(){
        $('#formCliente')[0].reset();
        $('#formCliente input[name=id]').val('');
        $('#clienteModal .modal-title').text('Cadastrar Cliente');
        $('#listaClienteModal').modal('hide');
        $('#clienteModal').modal('show');
    });

    $(document).on('click', '.btn-editar-cliente', function(){
        var tr = $(this).closest('tr');
        $('#formCliente input[name=id]').val(tr.data('id'));
        $('#formCliente input[name=cnpj]').val(tr.data('cnpj'));
        $('#formCliente input[name=nome]').val(tr.data('nome'));
        $('#clienteModal .modal-title').text('Editar Cliente');
        $('#listaClienteModal').modal('hide');
        $('#clienteModal').modal('show');
    });

    $(document).on('click', '.btn-excluir-cliente', function(){
        var tr = $(this).closest('tr');
        var id = tr.data('id');
        var nome = tr.data('nome');
        Swal.fire({
            title: 'Excluir "'+nome+'"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não'
        }).then(function(res){
            if(res.isConfirmed){
                $.post('excluir_cliente.php', {id:id}, function(resp){
                    if(resp.success){
                        atualizarKanban();
                    }
                }, 'json');
            }
        });
    });

    if(typeof clientesData !== 'undefined'){
        $('#clienteFiltro').on('keyup', function(){
            var termo = $(this).val().toLowerCase();
            $('#clienteDropdownMenu a.dropdown-item').each(function(){
                var txt = $(this).text().toLowerCase();
                $(this).toggle(txt.indexOf(termo) !== -1);
            });
        });

        $('#clienteDropdownMenu').on('click', 'a.dropdown-item', function(e){
            e.preventDefault();
            var nome = $(this).text();
            var id = $(this).data('id');
            $('#clienteDropdownBtn').text(nome);
            $('#cliente_id').val(id);
        });
    }

    // Filtro e paginação da lista de clientes
    var tamPagina = 5;
    var paginaAtual = 1;

    function atualizarPaginacao(){
        var linhas = $('#listaClienteModal tbody tr').not('.filtrado');
        var totalPaginas = Math.ceil(linhas.length / tamPagina) || 1;
        if(paginaAtual > totalPaginas){ paginaAtual = totalPaginas; }
        linhas.hide();
        linhas.slice((paginaAtual-1)*tamPagina, paginaAtual*tamPagina).show();
        $('#paginaAtual').text(paginaAtual + '/' + totalPaginas);
        $('#btnPrevCliente').prop('disabled', paginaAtual === 1);
        $('#btnNextCliente').prop('disabled', paginaAtual === totalPaginas);
    }

    $('#clienteBusca').on('keyup', function(){
        var termo = $(this).val().toLowerCase();
        $('#listaClienteModal tbody tr').each(function(){
            var cnpj = String($(this).data('cnpj')).toLowerCase();
            var nome = String($(this).data('nome')).toLowerCase();
            var match = cnpj.indexOf(termo) !== -1 || nome.indexOf(termo) !== -1;
            $(this).toggle(match).toggleClass('filtrado', !match);
        });
        paginaAtual = 1;
        atualizarPaginacao();
    });

    $('#btnPrevCliente').on('click', function(e){
        e.preventDefault();
        if(paginaAtual > 1){
            paginaAtual--;
            atualizarPaginacao();
        }
    });

    $('#btnNextCliente').on('click', function(e){
        e.preventDefault();
        var totalPaginas = Math.ceil($('#listaClienteModal tbody tr').not('.filtrado').length / tamPagina) || 1;
        if(paginaAtual < totalPaginas){
            paginaAtual++;
            atualizarPaginacao();
        }
    });

    $('#listaClienteModal').on('shown.bs.modal', function(){
        paginaAtual = 1;
        $('#clienteBusca').val('');
        $('#listaClienteModal tbody tr').show().removeClass('filtrado');
        atualizarPaginacao();
    });

    // Usuários
    $(document).on('click', '#btnNovoUsuario', function(){
        $('#formUsuario')[0].reset();
        $('#formUsuario input[name=id]').val('');
        $('#usuarioModal .modal-title').text('Cadastrar Usuário');
        $('#listaUsuarioModal').modal('hide');
        $('#usuarioModal').modal('show');
    });

    $(document).on('click', '.btn-editar-usuario', function(){
        var tr = $(this).closest('tr');
        $('#formUsuario input[name=id]').val(tr.data('id'));
        $('#formUsuario input[name=nome]').val(tr.data('nome'));
        $('#usuarioModal .modal-title').text('Editar Usuário');
        $('#listaUsuarioModal').modal('hide');
        $('#usuarioModal').modal('show');
    });

    $(document).on('click', '.btn-excluir-usuario', function(){
        var tr = $(this).closest('tr');
        var id = tr.data('id');
        var nome = tr.data('nome');
        Swal.fire({
            title: 'Excluir "'+nome+'"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não'
        }).then(function(res){
            if(res.isConfirmed){
                $.post('excluir_usuario.php', {id:id}, function(resp){
                    if(resp.success){
                        location.reload();
                    }
                }, 'json');
            }
        });
    });
});