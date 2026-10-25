<?php

return [
    'summary' => 'Corrige os campos indicados e tenta novamente.',
    'messages' => [
        'category_id' => [
            'exists' => 'A categoria selecionada já não está disponível.',
        ],
        'account_id' => [
            'required' => 'Seleciona a conta da transação recorrente.',
            'exists' => 'A conta selecionada já não está disponível.',
        ],
        'type' => [
            'required' => 'Seleciona o tipo de transação.',
            'in' => 'O tipo de transação deve ser despesa ou receita.',
        ],
        'amount' => [
            'required' => 'Indica o valor da transação recorrente.',
            'numeric' => 'O valor da transação deve ser um número.',
            'min' => 'O valor da transação deve ser igual ou superior a :min.',
        ],
        'title' => [
            'required' => 'Indica um título para a transação recorrente.',
            'string' => 'O título deve ser texto.',
            'max' => 'O título não pode ter mais de :max caracteres.',
        ],
        'description' => [
            'string' => 'As notas devem ser texto.',
        ],
        'frequency' => [
            'required' => 'Seleciona a frequência da transação recorrente.',
            'in' => 'Seleciona uma frequência válida: diária, semanal, mensal, anual ou personalizada.',
        ],
        'custom_frequency_days' => [
            'required_if' => 'Indica o intervalo em dias para a frequência personalizada.',
            'integer' => 'O intervalo de repetição deve ser um número inteiro de dias.',
            'min' => 'O intervalo de repetição deve ser de pelo menos :min dia.',
        ],
        'start_date' => [
            'required' => 'Indica a data de início da transação recorrente.',
            'date' => 'Indica uma data de início válida.',
        ],
        'end_date' => [
            'date' => 'Indica uma data de fim válida.',
            'after' => 'A data de fim deve ser posterior à data de início.',
        ],
        'is_linked' => [
            'boolean' => 'Indica se a transação está associada a uma dívida ou meta.',
        ],
        'linked_type' => [
            'required_if' => 'Seleciona o tipo de associação da transação recorrente.',
            'in' => 'A associação deve ser a uma dívida ou a uma meta financeira.',
        ],
        'linked_id' => [
            'required_if' => 'Seleciona a dívida ou meta financeira a associar.',
            'integer' => 'Seleciona uma dívida ou meta financeira válida.',
        ],
    ],
];
