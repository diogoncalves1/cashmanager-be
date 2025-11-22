<?php

return [
    'friendships' => [
        'alreadyFriendsException' => 'A amizade entre os usuários já existe.',
        'selfBlockNotAllowedException' => 'Você não pode se bloquear.',
        'userBlockedException' => 'Usuário bloqueado.',
        'selfFriendshipException' => 'Não é possível enviar, aceitar ou rejeitar solicitações para si mesmo.',
        'friendshipNotFoundException' => 'Esta amizade não existe ou já foi removida.',
        'userNotBlockedException' => 'Este usuário não está bloqueado.'
    ],

    'friendships-requests' => [
        'friendRequestAlreadySentException' => 'Solicitação de amizade já enviada.',
        'friendRequestLimitExceededException' => 'Você atingiu o número máximo de solicitações de amizade permitidas. Tente novamente mais tarde.',
        'friendRequestNotFoundException' => 'Solicitação de amizade não encontrada.',
        'senderCannotAcceptFriendRequestException' => 'O remetente não pode aceitar a própria solicitação de amizade.'
    ]
];
