<?php

return [
    'shareable' => [
        'alreadyRelationException' => 'A relação já existe.',
        'creatorInviteException' => 'Não é possível convidar um utilizador com a função de criador.',
        'creatorRevokeException' => 'Não é possível remover um utilizador com a função de criador da conta.',
        'inviteAlreadySentException' => 'O convite já foi enviado.',
        "inviteNotFoundException" => "Convite não encontrado.",
        'invitesLimitExceededException' => "Atingiu o número máximo de convites permitidos. Por favor, tente novamente mais tarde.",
        'inviteUserNotAllowedException' => 'Não é permitido convidar este utilizador.',
        'relationNotExistsException' => 'A relação especificada não existe.',
        'selfInviteException' => 'Não é possível enviar, aceitar ou rejeitar convites para si próprio.',
        'unauthorizedDestroyInviteException' => 'Não tem autorização para cancelar este convite.',
        'unauthorizedRevokeUserException' => 'Não tem autorização para revogar este utilizador.',
        "unauthorizedUpdateUserRoleException" => "Não tem permissão para atualizar a função do utilizador."
    ],
];
