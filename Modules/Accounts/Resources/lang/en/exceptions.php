<?php

return [

    'accounts'      => [
        'singleAccountCreatorViolationException' => 'You are not allowed to create more than one account.',
        'unauthorizedDeletedAccountException'    => 'You do not have permission to delete this account.',
        'unauthorizedUpdateAccountException'     => 'You do not have permission to update this account.',
        'unauthorizedViewAccount'                => 'You do not have permission to view this account.',
        'accountNotFoundException'               => 'The requested account could not be found.',
    ],

    'account-users' => [
        'creatorCantLeaveAccountException' => "The account creator cannot leave the account.",
    ],

    'transactions'  => [
        'cantConfirmTransactionCompletedException' => "Can't confirm the transaction will be completed.",
        'invalidTransactionPendingDateException'   => 'Cannot add a pending transaction with an invalid date.',
        'transactionAlreadyConfirmedException'     => 'Cannot confirm the transaction because it is already confirmed.',
        'invalidTransactionDateException'          => 'Impossível adicionar uma transação com a data maior que a atual sem estar agendada.',
        'transactionNotFoundException'             => 'The requested transaction could not be found.',
        'unauthorizedCreateTransactionException'   => 'You do not have permission to add transactions to this account.',
        'unauthorizedConfirmTransactionException'  => 'You do not have permission to confirm this transaction.',
        'unauthorizedDeletedTransactionException'  => 'You do not have permission to delete transactions from this account.',
        'unauthorizedUpdateTransactionException'   => 'You do not have permission to update this transaction.',
        'unauthorizedViewTransactionException'     => 'You do not have permission to view this transaction.',
    ],
];
