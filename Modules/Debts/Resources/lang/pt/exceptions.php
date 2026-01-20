<?php

return [
    'debts'         => [
        'debtNotFoundException'               => 'A dívida solicitada não foi encontrada.',
        'singleDebtCreatorViolationException' => 'Apenas um criador pode existir para esta dívida.',
        'debtNotInProgressException'          => 'Esta dívida não está atualmente em progresso.',
        'unauthorizedUpdateDebt'              => 'Não está autorizado a atualizar esta dívida.',
        'unauthorizedDeleteDebt'              => 'Não está autorizado a eliminar esta dívida.',
        'unauthorizedViewDebt'                => 'Não está autorizado a visualizar esta dívida.',
        'debtNotPendingException'             => 'Esta dívida não está pendente.',
        'debtNotFullyPaidException'           => 'Esta dívida não foi totalmente paga.',
        'debtPendingException'                => 'Esta dívida ainda está pendente e não é possível realizar esta ação.',
    ],

    'debt-payments' => [
        'debtPaymentNotFoundException'            => 'O pagamento da dívida solicitado não foi encontrado.',
        'unauthorizedUpdateDebtPaymentException'  => 'Não está autorizado a atualizar este pagamento da dívida.',
        'paymentBeforeCurrentDateException'       => 'A data do pagamento não pode ser anterior à data atual.',
        'unauthorizedCreateDebtPayment'           => 'Não está autorizado a criar um pagamento para esta dívida.',
        'unauthorizedDeleteDebtPaymentException'  => 'Não está autorizado a eliminar este pagamento da dívida.',
        'unauthorizedConfirmDebtPaymentException' => 'Não está autorizado a confirmar este pagamento da dívida.',
        'paymentNotScheduledException'            => 'Este pagamento não está agendado.',
        'unauthorizedViewDebtPaymentException'    => 'Não está autorizado a visualizar este pagamento da dívida.',
    ],
];
