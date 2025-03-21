<?php
return [
    'exports' => [
        'chunk_size' => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
            'output_encoding' => '',
        ],
    ],
    'import' => [
        'heading' => 'slugged',
        'skip_empty_rows' => true,
        'force_sheets_collection' => false,
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'input_encoding' => 'UTF-8',
        ],
    ],
    'extension_detector' => [
        'xlsx' => 'Xlsx',
        'csv' => 'Csv',
        'xls' => 'Xls',
    ],
];
