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