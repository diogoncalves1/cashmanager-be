<?php

return [
    'debts'         => [
        'debtNotFoundException'               => 'The requested debt could not be found.',
        'singleDebtCreatorViolationException' => '',
        'debtNotInProgressException'          => '',
        'unauthorizedUpdateDebt'              => '',
        'unauthorizedDeleteDebt'              => '',
        'unauthorizedViewDebt'                => '',
        'debtNotPendingException'             => '',
        'debtNotFullyPaidException'           => '',
        'debtPendingException'                => '',
    ],

    'debt-payments' => [
        'debtPaymentNotFoundException'            => '',
        'unauthorizedUpdateDebtPaymentException'  => '',
        'paymentBeforeCurrentDateException'       => '',
        'unauthorizedCreateDebtPayment'           => '',
        'unauthorizedDeleteDebtPaymentException'  => '',
        'unauthorizedConfirmDebtPaymentException' => '',
        'paymentNotScheduledException'            => '',
        'unauthorizedViewDebtPaymentException'    => '',
    ],
];
