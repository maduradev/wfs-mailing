<?php

return [
    'numbering' => [
        'prefix' => env('LETTER_NUMBER_PREFIX', 'SURAT'),
        'pattern' => env('LETTER_NUMBER_PATTERN', '{prefix}/{type}/{year}/{sequence}'),
        'sequence_length' => (int) env('LETTER_NUMBER_SEQUENCE_LENGTH', 4),
        'type_codes' => [
            'LEAVE' => 'CUTI',
            'MUTATION' => 'MUTASI',
            'WARNING' => 'PERINGATAN',
        ],
    ],
];
