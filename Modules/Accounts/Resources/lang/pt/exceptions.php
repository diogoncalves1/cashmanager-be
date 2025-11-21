<?php

return [

    // Accounts
    'accounts'      => [
        'singleAccountCreatorViolationException' => 'Não tem permissão para criar mais do que uma conta.',
        'unauthorizedDeletedAccountException'    => 'Não tem permissão para eliminar esta conta.',
        'unauthorizedUpdateAccountException'     => 'Não tem permissão para actualizar esta conta.',
        'unauthorizedViewAccount'                => 'Não tem permissão para visualizar esta conta.',
        'accountNotFoundException'               => 'A conta solicitada não foi encontrada.',
    ],

    // Account Users
    'account-users' => [
        'creatorCantLeaveAccountException' => 'O criador da conta não pode sair da conta.',
    ],

    // Transactions
    'transactions'  => [
        'cantConfirmTransactionCompletedException' => 'Não é possível confirmar que a transacção será concluída.',
        'invalidTransactionPendingDateException'   => 'Não é possível adicionar uma transacção pendente com uma data inválida.',
        'transactionAlreadyConfirmedException'     => 'Não é possível confirmar a transacção porque já se encontra confirmada.',
        'invalidTransactionDateException'          => 'Impossível adicionar uma transacção com data superior à actual sem que esteja agendada.',
        'transactionNotFoundException'             => 'A transacção solicitada não foi encontrada.',
        'unauthorizedCreateTransactionException'   => 'Não tem permissão para adicionar transacções a esta conta.',
        'unauthorizedConfirmTransactionException'  => 'Não tem permissão para confirmar esta transacção.',
        'unauthorizedDeletedTransactionException'  => 'Não tem permissão para eliminar transacções desta conta.',
        'unauthorizedUpdateTransactionException'   => 'Não tem permissão para actualizar esta transacção.',
        'unauthorizedViewTransactionException'     => 'Não tem permissão para visualizar esta transacção.',
    ],
];
