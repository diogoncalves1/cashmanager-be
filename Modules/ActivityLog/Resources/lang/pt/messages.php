<?php

return [

    'account'        => [
        'titles'   => [
            'account_created'        => 'Conta criada',
            'account_updated'        => 'Conta atualizada',
            'account_status_updated' => 'Estado atualizado',

            'transaction_added'      => 'Transação adicionada',
            'transaction_scheduled'  => 'Transação agendada',
            'transaction_updated'    => 'Transação atualizada',
            'transaction_deleted'    => 'Transação apagada',
            'transaction_confirmed'  => 'Transação confirmada',

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

            'transaction_added'      => ':type adicionada no dia :date com o valor de :amount',
            'transaction_scheduled'  => ':type agendada para dia :date com o valor de :amount',
            'transaction_updated'    => 'Transação atualizada: :changes',
            'transaction_deleted'    => 'Transação com valor de :amount foi apagada',
            'transaction_confirmed'  => 'Transação com valor de :amount agendada para :date foi confirmada',

            'user_invited'           => ':userName convidado como :roleName',
            'user_joined'            => 'Utilizador :userName entrou na conta como :roleName',
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
            'goal_paused'        => 'Meta cancelada',
            'contribution_added' => 'Contribuíção adicionada',
            'goal_reseted'       => 'Meta resetada',
            'goal_completed'     => 'Meta completada',

            'user_invited'       => 'Utilizador convidado',
            'user_joined'        => 'Utilizador novo',
            'invited_destroyed'  => 'Convite apagado',
            'invited_revoked'    => 'Convite recusado',
            'invited_revoked'    => 'Membro removido',
            'user_role_updated'  => 'Papel de membro atualizado',
            'user_leaved'        => 'Utilizador saiu',

        ],

        'messages' => [
            'goal_created'       => 'Meta inicial: :initialTarget',
            'contribution_added' => 'Contribuíção adicionada do valor de :amount da conta :accountName',
            'goal_paused'        => 'A meta financeira foi cancelada',
            'goal_reseted'       => 'A meta financeira voltou a estar pendente',
            'goal_completed'     => 'A meta financeira foi completa',

            'user_invited'       => ':userName convidado como :roleName',
            'user_joined'        => 'Utilizador :userName entrou na meta financeira como :roleName',
            'invited_destroyed'  => 'Convite para :userName apagado',
            'invited_revoked'    => ':userName foi removido',
            'user_role_updated'  => 'Papel de :userName foi atualizado para :roleName',
            'user_leaved'        => ':userName saiu da meta financeira',

        ],
    ],

    // Debts
    'debt'           => [
        'titles'   => [
            'debt_created'      => 'Dívida criada',

            'user_invited'      => 'Utilizador convidado',
            'user_joined'       => 'Utilizador novo',
            'invited_destroyed' => 'Convite apagado',
            'invited_revoked'   => 'Convite recusado',
            'invited_revoked'   => 'Membro removido',
            'user_role_updated' => 'Papel de membro atualizado',
            'user_leaved'       => 'Utilizador saiu',
        ],

        'messages' => [
            'debt_created'      => 'Dívida inicial: :initialAmount',

            'user_invited'      => ':userName convidado como :roleName',
            'user_joined'       => 'Utilizador :userName entrou na dívida como :roleName',
            'invited_destroyed' => 'Convite para :userName apagado',
            'invited_revoked'   => ':userName foi removido',
            'user_role_updated' => 'Papel de :userName foi atualizado para :roleName',
            'user_leaved'       => ':userName saiu da dívida',
        ],
    ],

    'format-changes' => [
        'support' => ':field de ":old" para ":new"',
    ],
];
