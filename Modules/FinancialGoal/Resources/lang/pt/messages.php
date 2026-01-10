<?php
return [
    // Metas financeiras
    'financial-goals'             => [
        'store'    => 'Meta financeira :name adicionada com sucesso!',
        'update'   => 'Meta financeira :name atualizada com sucesso!',
        'destroy'  => 'Meta financeira :name excluída com sucesso!',
        'cancel'   => 'Meta financeira :name cancelada com sucesso!',
        'complete' => 'Meta financeira :name concluída com sucesso!',
        'reset'    => 'Meta financeira :name voltou a estar em andamento!',

        // Erros
        'errors'   => [

        ],
    ],

    // Usuários da meta financeira
    'financial-goal-users'        => [
        'revokeUser'     => 'O usuário :userName foi removido da meta financeira :goalName.',
        'updateUserRole' => 'O cargo do usuário :userName foi atualizado para a meta financeira :goalName.',
        'leave'          => 'Saiu da meta financeira :goalName com sucesso.',

        'errors'         => [
            'revokeUser'     => 'Falha ao remover o usuário da meta financeira.',
            'updateUserRole' => 'Falha ao atualizar o cargo do usuário para a meta financeira.',
            'leave'          => 'Falha ao sair da meta financeira.',
        ],
    ],

    // Convites para meta financeira
    'financial-goal-user-invites' => [
        'invite'  => 'O usuário :userName foi convidado com sucesso para a meta financeira :goalName.',
        'accept'  => 'Convite para a meta financeira :name aceito com sucesso.',
        'destroy' => 'O convite para a meta financeira :goalName do usuário :userName foi excluído com sucesso.',
        'revoke'  => 'Convite para a meta financeira :goalName recusado.',

        'errors'  => [
            'invite'  => 'Falha ao convidar o usuário.',
            'accept'  => 'Falha ao aceitar o convite.',
            'destroy' => 'Falha ao excluir o convite.',
            'revoke'  => 'Falha ao recusar o convite.',
        ],
    ],

    // Transações da meta financeira
    'financial-goal-transactions' => [
        'store'   => 'Transação da meta financeira adicionada à meta :name com sucesso!',
        'update'  => 'Transação da meta financeira atualizada na meta :name com sucesso!',
        'destroy' => 'Transação da meta financeira excluída da meta :name com sucesso!',
        'confirm' => 'Transação da meta financeira confirmada na meta :name com sucesso!',
    ],
];
