<?php

return [

    'account'        => [
        'titles'   => [
            'account_created'        => 'Conta criada',
            'account_updated'        => 'Conta atualizada',
            'account_status_updated' => 'Estado atualizado',

            'user_invited'           => 'Utilizador convidado',
            'user_joined'            => 'Utilizador novo',
            'invited_destroyed'      => 'Convite apagado',
            'invited_revoked'        => 'Convite recusado',
            'invited_revoked'        => 'Membro removido',
            'user_role_updated'      => 'Papel de membro atualizado',
            'user_leaved'            => 'Utilizador saiu',
        ],

        'messages' => [
            'account_created'        => 'Conta criada: :accountName',
            'account_updated'        => 'Conta atualizada: :changes',
            'account_status_updated' => 'Estado atualizado para :status',

            'user_invited'           => ':userName convidado como :roleName',
            'user_joined'            => 'Utilizador :userName entrou na dívida como :roleName',
            'invited_destroyed'      => 'Convite para :userName apagado',
            'invited_revoked'        => ':userName foi removido',
            'user_role_updated'      => 'Papel de :userName foi atualizado para :roleName',
            'user_leaved'            => ':userName saiu da conta',
        ],
    ],

    // Financial Goals
    'financial_goal' => [
        'titles'   => [
            'goal_created'       => 'Meta criada',
            'user_invited'       => 'Utilizador convidado',
            'goal_paused'        => 'Meta cancelada',
            'contribution_added' => 'Contribuíção adicionada',
            'goal_reseted'       => 'Meta resetada',
            'goal_completed'     => 'Meta completada',

        ],

        'messages' => [
            'goal_created'       => 'Meta inicial: :initialTarget',
            'contribution_added' => 'Contribuíção adicionada do valor de :amount da conta :accountName',
            'user_invited'       => ':userName convidado como :roleName',
            'goal_paused'        => 'A meta financeira foi cancelada',
            'goal_reseted'       => 'A meta financeira voltou a estar pendente',
            'goal_completed'     => 'A meta financeira foi completa',

        ],
    ],

    // Debts
    'debt'           => [
        'titles'   => [
            'debt_created' => 'Dívida criada',
            'user_joined'  => 'Utilizador novo',
        ],

        'messages' => [
            'debt_created' => 'Dívida inicial: :initialAmount',
            'user_joined'  => 'Utilizador :userName entrou na dívida como :roleName',
        ],
    ],

    'format-changes' => [
        'support' => ':field de ":old" para ":new"',
    ],
];
