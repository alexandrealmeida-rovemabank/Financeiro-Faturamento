# Sistema de Faturamento - Módulo Contas a Receber (Sigyo)

[cite_start]Este projeto é um módulo avançado de Contas a Receber (Faturamento) construído em **Laravel 10** [cite: 1] e integrado ao ecossistema de banco de dados Sigyo.

[cite_start]O objetivo principal deste módulo é automatizar o processo de faturamento, processando transações de vendas (originadas no schema `public`) [cite: 95] [cite_start]e consolidando-as em um novo schema (`contas_receber`)[cite: 112], permitindo a aplicação de regras de negócio complexas, gerenciamento de parâmetros e auditoria completa.

---

## Principais Funcionalidades

* **Controle de Acesso (RBAC):** Gerenciamento completo de Usuários, Perfis (Roles) e Permissões através de uma interface administrativa.
* [cite_start]**Gestão de Clientes e Unidades:** Visualização detalhada de clientes (Matriz) e suas Unidades (filiais) vinculadas, com dados cadastrais, contratos e empenhos. [cite: 181]
* **Gestão de Credenciados:** Funcionalidade similar à de Clientes, separando Credenciados Matriz de suas Unidades.
* [cite_start]**Parâmetros Configuráveis:** Sistema duplo de parâmetros (Globais e por Cliente/Unidade) para definir regras de faturamento, como prazos de vencimento e aplicação de IR. [cite: 92, 116]
* **Processamento de Transações (Jobs):** Comandos robustos para sincronização diária de dados, reprocessamento geral e reprocessamento personalizado por cliente/período, executados em fila para alta performance.
* [cite_start]**Auditoria e Monitoramento:** Logs detalhados de todas as ações de usuários (login, logout, CRUD) [cite: 215] e um log de monitoramento dedicado para os processos de sincronização de transações.

---

## Stack de Tecnologia

* [cite_start]**Framework:** PHP 8.2 / Laravel 10 [cite: 1]
* [cite_start]**Banco de Dados:** PostgreSQL com arquitetura de Schemas Múltiplos (`public`, `contas_receber`, `contas_pagar`) [cite: 95, 96, 112]
* **Frontend:** AdminLTE (via `jeroennoten/laravel-adminlte`)
* **Tabelas Dinâmicas:** DataTables (com processamento Server-Side)
* **Filas (Queues):** Driver de Banco de Dados (para processamento em segundo plano)
* **Pacotes Principais:**
    * `spatie/laravel-permission`: Para controle de acesso (RBAC).
    * `spatie/laravel-activitylog`: Para auditoria de atividades de usuário.
    * `yajra/laravel-datatables-oracle`: Para integração server-side do DataTables.

---

## Instalação e Configuração

1.  **Clone o repositório:**
    ```bash
    git clone [URL_DO_REPOSITORIO]
    cd financeiro_sigyo
    ```

2.  **Instale as dependências:**
    ```bash
    composer install
    npm install
    ```

3.  **Configure o Ambiente (`.env`):**
    Copie `.env.example` para `.env` e configure as conexões de banco de dados (`pgsql`), schemas e o driver de fila:
    ```ini
    DB_CONNECTION=pgsql
    DB_HOST=localhost
    DB_PORT=5432
    DB_DATABASE=financeiro
    DB_USERNAME=postgres
    DB_PASSWORD=sua_senha

    # Define o search_path padrão do PostgreSQL
    DB_SCHEMA="contas_receber,public"

    # Configura o driver de fila para 'database' para processamento em background
    QUEUE_CONNECTION=database
    ```

4.  **Gere a Chave da Aplicação:**
    ```bash
    php artisan key:generate
    ```

5.  **Configure o Banco de Dados:**
    * **Tabela de Migrations:** Edite `config/database.php` e, na conexão `pgsql`, informe ao Laravel que a tabela `migrations` está no schema `contas_receber`:
        ```php
        'pgsql' => [
            // ...
            'search_path' => env('DB_SCHEMA', 'public'),
            'migrations' => 'contas_receber.migrations', // <--- Adicione esta linha
        ],
        ```
    * **Tabela de Jobs:** Crie a tabela para o driver de fila:
        ```bash
        php artisan queue:table
        ```
    * **Tabela de Logs:** Publique as migrations dos pacotes:
        ```bash
        php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
        ```
    * **Ajuste as Migrações:** Modifique os arquivos de migration recém-publicados (de `activitylog` e os seus próprios, como `create_parametro_cliente_table`, etc.) para que todos sejam criados no schema `contas_receber.`:
        ```php
        // Exemplo:
        Schema::create('contas_receber.parametro_cliente', function (Blueprint $table) { ... });
        ```

6.  **Execute as Migrações e Seeders:**
    Isso criará as tabelas do módulo (no schema `contas_receber`) e populará os perfis e permissões iniciais.
    ```bash
    php artisan migrate
    php artisan db:seed --class=PermissionsAndRolesSeeder
    ```

7.  **Compile os Assets:**
    ```bash
    npm run dev
    ```

---

## Fluxo de Dados e Arquitetura de Schemas

O sistema opera com três schemas distintos:

* **`public` (Leitura):** Fonte da verdade para dados brutos. [cite_start]Contém tabelas como `empresa`, `transacao`, `produto`, `organizacao`, `produto_categoria`, `contrato`, `empenho`, etc. [cite: 95, 104]
* [cite_start]**`contas_pagar` (Leitura):** Fonte das regras de negócio fiscais, principalmente a `vw_contas_a_pagar` que fornece os cálculos de IR. [cite: 96, 109, 111]
* **`contas_receber` (Escrita):** Schema principal deste módulo. Armazena todos os dados processados e as configurações. Contém tabelas como:
    * `transacao_faturamento`: Cópia processada das transações.
    * `parametro_cliente`: Configurações específicas por cliente/unidade.
    * `parametro_global`: Configurações padrão do sistema.
    * `parametro_taxa_aliquota`: Regras de taxas por Categoria/Organização.
    * `processamento_log`: Log de execução dos jobs de sincronização.
    * `activity_log`: Log de auditoria de ações dos usuários.
    * `migrations`: Controle de versão do banco de dados do módulo.
    * `jobs`: Tabela para a fila de processamento.

---

## Módulos Detalhados

### 1. Administração (Controle de Acesso)

* **Usuários:** CRUD de usuários do sistema.
* **Perfis de Acesso (Roles):** CRUD de perfis (ex: Admin, Financeiro).
* **Permissões:** CRUD de permissões (ex: `view cliente`, `edit parametros globais`).
* **Lógica:** Protegido por `spatie/laravel-permission`. Apenas usuários com privilégios de `admin` podem atribuir o perfil de `admin` a outros, e usuários não podem alterar seus próprios perfis.

### 2. Clientes e Credenciados

* **Listagem (Index):** Ambas as telas (Clientes e Credenciados) utilizam DataTables server-side para listar apenas as **Matrizes** (tipo 1 para Clientes, tipo 3 para Credenciados).
* **Expansão de Unidades:** Um ícone `+` aparece ao lado das matrizes que possuem unidades. Ao clicar, uma chamada AJAX busca e exibe uma sub-tabela com todas as **Unidades** (tipo 2 ou 4) vinculadas àquela matriz.
* **Detalhes (Show):** Uma tela completa que exibe:
    * **Abas da Matriz:** Informações Gerais, Contratos, Empenhos e Parâmetros.
    * **Acordeão de Unidades:** Uma lista expansível de todas as unidades, onde cada unidade, ao ser aberta, exibe seu próprio conjunto de abas (Informações Gerais, Contratos, Empenhos, Parâmetros).

### 3. Parâmetros (Globais e por Cliente)

* **Parâmetros Globais:** (Local: `/admin/parametros-globais`)
    * Permite definir as regras padrão do sistema: Descontar IR, Prazo Público (dias), Prazo Privado (dias).
    * Permite gerenciar a tabela `parametro_taxa_aliquota`, definindo taxas por **Organização** e **Categoria de Produto**.
* **Parâmetros do Cliente:** (Local: Aba "Parâmetros" na tela de detalhes do Cliente)
    * Permite sobrescrever as regras globais para um cliente ou unidade específica.
    * Um switch "Utilizar Parâmetros Globais" controla quais regras são aplicadas.

### 4. Processamento de Transações (Jobs & Logs)

Esta é a funcionalidade central de sincronização de dados.

* **Comandos:**
    * `faturamento:processar-transacoes`: (Agendado diariamente) Copia apenas transações novas (`ID > max(ID)`).
    * `faturamento:reprocessar-geral`: (Manual) Executa `TRUNCATE` na tabela `transacao_faturamento` e copia 100% dos dados da origem.
    * `faturamento:reprocessar-personalizado`: (Manual) Executa `DELETE` com base nos filtros (Cliente/Unidade, Data, Escopo) e recopia os dados filtrados.
* **Execução em Fila (Queue):**
    * Para evitar timeouts em requisições web, todos os acionamentos manuais usam um Job Wrapper (`ExecutarComandoArtisanJob`).
    * Este Job define um timeout longo (ex: 2 horas) e chama o comando Artisan apropriado em segundo plano.
* **Monitoramento (Logs):**
    * A tabela `processamento_log` registra o início, fim, status (iniciado, processando, sucesso, falha), contagem de transações, IDs processados e mensagens de erro de cada execução.
    * A tela `/admin/processamento-logs` exibe esses logs e permite o acionamento manual (Últimas, Geral, Personalizado).

---

## Execução (Ambiente de Desenvolvimento)

Para rodar o projeto localmente, são necessários dois processos de terminal:

1.  **Servidor Web (Vite):**
    ```bash
    npm run dev
    ```

2.  **Servidor PHP (Artisan):**
    ```bash
    php artisan serve
    ```

3.  **Processador da Fila (Queue Worker):**
    Este é **obrigatório** para que os reprocessamentos manuais funcionem. Execute em um terminal separado:
    ```bash
    # O --timeout=7200 (2 horas) é essencial para tarefas longas
    php artisan queue:work --timeout=7200
    ```

## Agendamento de Tarefas (Scheduler)

Para que o processamento diário (`faturamento:processar-transacoes`) funcione em produção, o Scheduler do Laravel precisa ser configurado. Adicione a seguinte entrada ao Crontab do seu servidor:

```cron
* * * * * cd /caminho/para/seu/projeto && php artisan schedule:run >> /dev/null 2>&1