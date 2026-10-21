<?php

return [
    'friendship-requests' => [
        'send'    => 'Pedido de amizade enviado com sucesso para :name.',
        'accept'  => 'Pedido de amizade de :name aceite com sucesso.',
        'decline' => 'Pedido de amizade de :name rejeitado com sucesso.',
        'cancel'  => 'Pedido de amizade para :name cancelado com sucesso.',
        'errors'  => [
            'send'    => 'Não foi possível enviar o pedido de amizade.',
            'accept'  => 'Não foi possível aceitar o pedido de amizade.',
            'decline' => 'Não foi possível rejeitar o pedido de amizade.',
            'cancel'  => 'Não foi possível cancelar o pedido de amizade.',
        ],
    ],

    'friendships'         => [
        'remove'  => 'Amizade encerrada com sucesso.',
        'block'   => ':name bloqueado(a) com sucesso.',
        'unblock' => ':name desbloqueado(a) com sucesso.',
        'errors'  => [
            'remove'  => 'Erro ao tentar encerrar a amizade.',
            'block'   => 'Não foi possível bloquear o utilizador.',
            'unblock' => 'Não foi possível desbloquear o utilizador.',
        ],
    ],
];
