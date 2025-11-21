<?php
return [
    // Accounts
    'accounts'             => [
        'store'   => 'Conta :name adicionada com sucesso!',
        'update'  => 'Conta :name actualizada com sucesso!',
        'destroy' => 'Conta :name eliminada com sucesso!',

        'errors'  => [
            'store'   => 'Erro ao tentar adicionar a conta.',
            'update'  => 'Erro ao tentar actualizar a conta.',
            'destroy' => 'Erro ao tentar eliminar a conta.',
        ],
    ],

    // Account Users
    'account-users'        => [
        'revokeUser'     => 'O utilizador :userName foi removido da conta :accountName.',
        'updateUserRole' => 'O papel do utilizador :userName foi actualizado na conta :accountName.',
        'leave'          => 'Saiu da conta :accountName com sucesso.',

        'errors'         => [
            'revokeUser'     => 'Falha ao remover o utilizador da conta.',
            'updateUserRole' => 'Falha ao actualizar o papel do utilizador na conta.',
            'leave'          => 'Falha ao sair da conta.',
        ],
    ],

    // Account User Invites
    'account-user-invites' => [
        'invite'  => 'O utilizador :userName foi convidado com sucesso para a conta :accountName.',
        'accept'  => 'Convite para a conta :name aceite com sucesso.',
        'destroy' => 'O convite para a conta :accountName do utilizador :userName foi eliminado com sucesso.',
        'revoke'  => 'Convite para a conta :accountName recusado.',

        'errors'  => [
            'invite'  => 'Falha ao convidar o utilizador.',
            'accept'  => 'Falha ao aceitar o convite.',
            'destroy' => 'Falha ao eliminar o convite.',
            'revoke'  => 'Falha ao recusar o convite.',
        ],
    ],

    // Transaction
    'transactions'         => [
        'store'   => 'Transacção adicionada com sucesso!',
        'update'  => 'Transacção actualizada com sucesso!',
        'destroy' => 'Transacção eliminada com sucesso!',
        'confirm' => 'Transacção confirmada com sucesso!',

        'errors'  => [
            'store'   => 'Erro ao tentar adicionar a transacção.',
            'update'  => 'Erro ao tentar actualizar a transacção.',
            'destroy' => 'Erro ao tentar eliminar a transacção.',
            'confirm' => 'Erro ao tentar confirmar a transacção.',
        ],
    ],

];
