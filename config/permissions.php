<?php
return [
    [
        'label' => 'Usuários',
        'description' => 'Permissões relacionadas à gestão de usuários',
        'permissions' => [
            'view users',
            'create users',
            'edit users',
            'delete users',
        ],
    ],
    [
        'label' => 'Roles e Permissões',
        'description' => 'Gerenciar papéis e permissões do sistema',
        'permissions' => [
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'assign roles to users',
            'assign permissions to roles',
            'assign direct permissions to users',
        ],
    ],
    [
        'label' => 'Clientes',
        'description' => 'Permissões relacionadas a clientes',
        'permissions' => [
            'view cliente',
            'show cliente',
            'edit cliente',
        ],
    ],
    [
        'label' => 'Credenciados',
        'description' => 'Permissões relacionadas a credenciados',
        'permissions' => [
            'view credenciado',
            'show credenciado',
            'edit credenciado',
        ],
    ],
        [
        'label' => 'logs sistema',
        'description' => 'Permissões relacionadas a credenciados',
        'permissions' => [
            'view logs'
        ],
    ],
    [
        'label' => 'Faturamento',
        'description' => 'Permissões relacionadas ao faturamento',
        'permissions' => [
            'view faturamento',
            'show faturamento',
            'edit faturamento',
            'delete faturamento',
        ],
    ],
    [
        'label' => 'Reprocessamento',
        'description' => 'Permissões relacionadas ao reprocessamento de faturamento',
        'permissions' => [
            'view reprocessamento',
            'run reprocessamento geral',
            'run reprocessamento personalizado',
            'run reprocessamento ultimas transações',
        ],
    ],
    [
        'label' => 'Cobrança',
        'description' => 'Permissões relacionadas à cobrança',
        'permissions' => [
            'view cobranca',
            'show cobranca',
            'edit cobranca',
            'delete cobranca',
        ],
    ],
    [
        'label' => 'Parâmetros Globais',
        'description' => 'Permissões de configuração global do sistema',
        'permissions' => [
            'view parametros globais',
            'edit parametros globais',
            'reset parametros globais',
            'create parametros globais',
            'delete parametros globais',
        ],
    ],
    [
        'label' => 'Relatórios',
        'description' => 'Permissões relacionadas à geração de relatórios',
        'permissions' => [
            'view relatorios',
            'generate relatorios',
        ],
    ],
];
