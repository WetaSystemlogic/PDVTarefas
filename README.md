# PDVTarefas

Projeto simples de controle de tarefas em PHP com layout responsivo utilizando Bootstrap. Agora as tarefas possuem registro de data/hora de criação e atualização, além de mudança de situação via arraste.

## Estrutura de Pastas
```
/PDVTarefas
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── db/
│   └── pdvtarefas.db (gerado pelo init_db.php)
├── config.php
├── detalhes_tarefa.php
├── index.php
├── init_db.php
├── salvar_tarefa.php
└── README.md
```

## Como Utilizar
1. Certifique-se de ter o PHP instalado em seu ambiente.
2. Execute `php init_db.php` para criar o banco de dados SQLite e as tabelas necessárias.
3. Acesse `index.php` em seu servidor web para visualizar o kanban e cadastrar novas tarefas.

As cores principais do layout são `#131f45` e branco, garantindo boa visualização em desktop e dispositivos móveis.

### Favicon
O arquivo `favicon.ico` já está incluído na raiz do projeto. A página `index.php` referencia esse ícone no elemento `<head>` para exibição pelo navegador. Caso deseje personalizar o ícone, substitua o arquivo `favicon.ico` por outro de sua escolha e recarregue a aplicação.

### Status Finalizado
Quando a situação de uma tarefa é alterada para **Finalizado**, o indicador de prazo deixa de ser calculado e passa a mostrar o texto **Finalizado**.

### Arquivar tarefas
Tarefas finalizadas podem ser arquivadas clicando no ícone de arquivo no card. Arquivadas não aparecem no kanban e ficam listadas no botão **Arquivadas** no topo da página.

### Arquivamento automático
No sábado, o sistema verifica as tarefas com situação **Finalizado** e as move
automaticamente para o status **Arquivada**. Essa verificação é realizada
sempre que qualquer página carrega o arquivo `config.php`.

### Lembretes de agendamento
O script `send_reminders.php` envia mensagens de WhatsApp quando uma tarefa
agendada está a 30, 20 ou 10 minutos de iniciar. Para utilizá‑lo,
agende a execução desse arquivo via `cron` ou ferramenta similar.