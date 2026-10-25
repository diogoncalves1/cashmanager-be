<?php

return [
    'summary' => 'Correct the highlighted fields and try again.',
    'messages' => [
        'category_id' => [
            'exists' => 'The selected category is no longer available.',
        ],
        'account_id' => [
            'required' => 'Select an account for the recurring transaction.',
            'exists' => 'The selected account is no longer available.',
        ],
        'type' => [
            'required' => 'Select a transaction type.',
            'in' => 'The transaction type must be expense or revenue.',
        ],
        'amount' => [
            'required' => 'Enter the recurring transaction amount.',
            'numeric' => 'The transaction amount must be a number.',
            'min' => 'The transaction amount must be at least :min.',
        ],
        'title' => [
            'required' => 'Enter a title for the recurring transaction.',
            'string' => 'The title must be text.',
            'max' => 'The title must not exceed :max characters.',
        ],
        'description' => [
            'string' => 'The notes must be text.',
        ],
        'frequency' => [
            'required' => 'Select the recurring transaction frequency.',
            'in' => 'Select a valid frequency: daily, weekly, monthly, yearly or custom.',
        ],
        'custom_frequency_days' => [
            'required_if' => 'Enter the interval in days for the custom frequency.',
            'integer' => 'The repeat interval must be a whole number of days.',
            'min' => 'The repeat interval must be at least :min day.',
        ],
        'start_date' => [
            'required' => 'Enter the recurring transaction start date.',
            'date' => 'Enter a valid start date.',
        ],
        'end_date' => [
            'date' => 'Enter a valid end date.',
            'after' => 'The end date must be after the start date.',
        ],
        'is_linked' => [
            'boolean' => 'Specify whether the transaction is linked to a debt or goal.',
        ],
        'linked_type' => [
            'required_if' => 'Select the recurring transaction link type.',
            'in' => 'The transaction must link to a debt or a financial goal.',
        ],
        'linked_id' => [
            'required_if' => 'Select the debt or financial goal to link.',
            'integer' => 'Select a valid debt or financial goal.',
        ],
    ],
];
