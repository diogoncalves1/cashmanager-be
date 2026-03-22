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

    // Debts
    'debt'           => [
        'titles'   => [
            'debt_created'        => 'Dívida criada',
            'debt_updated'        => 'Dívida atualizada',
            'debt_status_updated' => 'Estado atualizado',

            'payment_added'       => 'Pagamento adicionado',
            'payment_scheduled'   => 'Pagamento agendado',
            'payment_updated'     => 'Pagamento atualizado',
            'payment_deleted'     => 'Pagamento apagado',
            'payment_confirmed'   => 'Pagamento confirmado',

            'user_invited'        => 'Utilizador convidado',
            'user_joined'         => 'Utilizador novo',
            'invited_destroyed'   => 'Convite apagado',
            'invited_revoked'     => 'Convite recusado',
            'invited_revoked'     => 'Membro removido',
            'user_role_updated'   => 'Papel de membro atualizado',
            'user_leaved'         => 'Utilizador saiu',
        ],

        'messages' => [
            'debt_created'        => 'Dívida inicial: :initialAmount',
            'debt_updated'        => 'Conta atualizada: :changes',
            'debt_status_updated' => 'Estado atualizado para: :status',

            'payment_added'       => 'Pagamento adicionado no dia :date com o valor de :amount, com taxa de juro de :interest_rate%. Foi um pagamento mensal: :is_monthly_payment',
            'payment_scheduled'   => 'Pagamento agendado para dia :date com o valor de :amount, com taxa de juro de :interest_rate%. Será um pagamento mensal: :is_monthly_payment',
            'payment_updated'     => 'Pagamento atualizado: :changes',
            'payment_deleted'     => 'Pagamento com valor de :amount de dia :date foi apagado',
            'payment_confirmed'   => 'Pagamento com valor de :amount agendado para :date foi confirmado',

            'user_invited'        => ':userName convidado como :roleName',
            'user_joined'         => 'Utilizador :userName entrou na dívida como :roleName',
            'invited_destroyed'   => 'Convite para :userName apagado',
            'invited_revoked'     => ':userName foi removido',
            'user_role_updated'   => 'Papel de :userName foi atualizado para :roleName',
            'user_leaved'         => ':userName saiu da dívida',
        ],
    ],

    // Financial Goals
    'financial_goal' => [
        'titles'   => [
            'goal_created'          => 'Meta criada',
            'goal_updated'          => 'Meta atualizada',
            'goal_status_updated'   => 'Estado atualizado',

            'transaction_added'     => 'Transação adicionada',
            'transaction_scheduled' => 'Transação agendada',
            'transaction_updated'   => 'Transação atualizada',
            'transaction_deleted'   => 'Transação apagada',
            'transaction_confirmed' => 'Transação confirmada',

            'user_invited'          => 'Utilizador convidado',
            'user_joined'           => 'Utilizador novo',
            'invited_destroyed'     => 'Convite apagado',
            'invited_revoked'       => 'Convite recusado',
            'invited_revoked'       => 'Membro removido',
            'user_role_updated'     => 'Papel de membro atualizado',
            'user_leaved'           => 'Utilizador saiu',

        ],

        'messages' => [
            'goal_created'          => 'Meta inicial: :initialTarget',
            'goal_updated'          => 'Meta atualizada: :changes',
            'goal_status_updated'   => 'Estado atualizado para: :status',

            'transaction_added'     => ':type adicionada no dia :date com o valor de :amount',
            'transaction_scheduled' => ':type agendada para dia :date com o valor de :amount',
            'transaction_updated'   => 'Transação atualizada: :changes',
            'transaction_deleted'   => 'Transação com valor de :amount foi apagada',
            'transaction_confirmed' => 'Transação com valor de :amount agendada para :date foi confirmada',

            'user_invited'          => ':userName convidado como :roleName',
            'user_joined'           => 'Utilizador :userName entrou na meta financeira como :roleName',
            'invited_destroyed'     => 'Convite para :userName apagado',
            'invited_revoked'       => ':userName foi removido',
            'user_role_updated'     => 'Papel de :userName foi atualizado para :roleName',
            'user_leaved'           => ':userName saiu da meta financeira',

        ],
    ],

    'format-changes' => [
        'support' => ':field de ":old" para ":new"',
    ],
];
