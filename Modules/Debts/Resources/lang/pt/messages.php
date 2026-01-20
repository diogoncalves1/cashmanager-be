<?php
return [
    // Dívidas
    'debts'             => [
        'store'     => 'Dívida :name adicionada com sucesso!',
        'update'    => 'Dívida :name atualizada com sucesso!',
        'destroy'   => 'Dívida :name eliminada com sucesso!',
        'mark-paid' => 'Dívida :name marcada como paga com sucesso!',
        'reset'     => 'Dívida :name reiniciada com sucesso!',

        'errors'    => [
            'store'     => 'Erro ao tentar adicionar a dívida.',
            'update'    => 'Erro ao tentar atualizar a dívida.',
            'destroy'   => 'Erro ao tentar eliminar a dívida.',
            'mark-paid' => 'Erro ao tentar marcar a dívida como paga.',
            'reset'     => 'Erro ao tentar reiniciar a dívida.',
        ],
    ],

    // Utilizadores da dívida
    'debt-users'        => [
        'revokeUser'     => 'O utilizador :userName foi removido da dívida :debtName.',
        'updateUserRole' => 'O papel do utilizador :userName foi atualizado na dívida :debtName.',
        'leave'          => 'Saiu da dívida :debtName com sucesso.',

        'errors'         => [
            'revokeUser'     => 'Falha ao remover o utilizador da dívida.',
            'updateUserRole' => 'Falha ao atualizar o papel do utilizador na dívida.',
            'leave'          => 'Falha ao sair da dívida.',
        ],
    ],

    // Convites de utilizadores para a dívida
    'debt-user-invites' => [
        'invite'  => 'O utilizador :userName foi convidado com sucesso para a dívida :debtName.',
        'accept'  => 'Convite para a dívida :name aceite com sucesso.',
        'destroy' => 'O convite para a dívida :debtName do utilizador :userName foi eliminado com sucesso.',
        'revoke'  => 'Convite para a dívida :debtName rejeitado.',

        'errors'  => [
            'invite'  => 'Falha ao convidar o utilizador.',
            'accept'  => 'Falha ao aceitar o convite.',
            'destroy' => 'Falha ao eliminar o convite.',
            'revoke'  => 'Falha ao rejeitar o convite.',
        ],
    ],

    // Pagamentos de dívidas
    'debt-payments'     => [
        'store'   => 'Pagamento da dívida adicionado com sucesso!',
        'update'  => 'Pagamento da dívida atualizado com sucesso!',
        'destroy' => 'Pagamento da dívida eliminado com sucesso!',
        'confirm' => 'Pagamento da dívida confirmado com sucesso!',

        'errors'  => [
            'store'   => 'Erro ao tentar adicionar o pagamento da dívida.',
            'update'  => 'Erro ao tentar atualizar o pagamento da dívida.',
            'destroy' => 'Erro ao tentar eliminar o pagamento da dívida.',
            'confirm' => 'Erro ao tentar confirmar o pagamento da dívida.',
        ],
    ],
];
