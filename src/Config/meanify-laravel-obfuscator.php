<?php

return [
    'length'    => env('MEANIFY_LARAVEL_OBFUSCATOR_LENGTH', 10),
    'log_to_db' => env('MEANIFY_LARAVEL_OBFUSCATOR_LOG_TO_DB', false),
    'table'     => 'obfuscator_failures',
];
