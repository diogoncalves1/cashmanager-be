<?php

return [
    'shareable' => [
        'alreadyRelationException'            => 'A relação já existe.',
        'creatorCantLeaveException'           => 'O criador da conta não pode sair da conta.',
        'creatorInviteException'              => 'Não é possível convidar um utilizador com o papel de criador.',
        'creatorRevokeException'              => 'Não é possível remover um utilizador com o papel de criador da conta.',
        'inviteAlreadySentException'          => 'Convite já enviado.',
        'inviteNotFoundException'             => 'Convite não encontrado.',
        'invitesLimitExceededException'       => 'Alcançou o número máximo de convites permitidos. Por favor, tente novamente mais tarde.',
        'inviteUserNotAllowedException'       => 'Não é permitido convidar este utilizador.',
        'relationNotExistsException'          => 'A relação especificada não existe.',
        'selfInviteException'                 => 'Não é possível enviar, aceitar ou recusar qualquer convite para si próprio.',
        'selfRoleUpdateNotAllowedException'   => 'Não tem permissão para actualizar o seu próprio papel na conta.',
        'unauthorizedDestroyInviteException'  => 'Não tem autorização para cancelar este convite.',
        'unauthorizedRevokeUserException'     => 'Não tem autorização para revogar este utilizador.',
        'unauthorizedUpdateUserRoleException' => 'Não tem permissão para actualizar o papel do utilizador.',
    ],
];
