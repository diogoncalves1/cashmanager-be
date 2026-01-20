<?php

return [
    'debts'         => [
        'debtNotFoundException'               => 'The requested debt could not be found.',
        'singleDebtCreatorViolationException' => 'Only one creator of this debt.',
        'debtNotInProgressException'          => 'This debt is not currently in progress.',
        'unauthorizedUpdateDebt'              => 'You are not authorized to update this debt.',
        'unauthorizedDeleteDebt'              => 'You are not authorized to delete this debt.',
        'unauthorizedViewDebt'                => 'You are not authorized to view this debt.',
        'debtNotPendingException'             => 'This debt is not pending.',
        'debtNotFullyPaidException'           => 'This debt has not been fully paid.',
        'debtPendingException'                => 'This debt is still pending and cannot perform this action.',
    ],

    'debt-payments' => [
        'debtPaymentNotFoundException'            => 'The requested debt payment could not be found.',
        'unauthorizedUpdateDebtPaymentException'  => 'You are not authorized to update this debt payment.',
        'paymentBeforeCurrentDateException'       => 'The payment date cannot be before the current date.',
        'unauthorizedCreateDebtPayment'           => 'You are not authorized to create a debt payment.',
        'unauthorizedDeleteDebtPaymentException'  => 'You are not authorized to delete this debt payment.',
        'unauthorizedConfirmDebtPaymentException' => 'You are not authorized to confirm this debt payment.',
        'paymentNotScheduledException'            => 'This payment is not scheduled.',
        'unauthorizedViewDebtPaymentException'    => 'You are not authorized to view this debt payment.',
    ],
];
