<?php

return [
    'length' =>  env('MEANIFY_LARAVEL_OBFUSCATOR_LENGTH', 12),

    'alphabetic' => env('MEANIFY_LARAVEL_OBFUSCATOR_ALPHABETIC', 'ABCDEF1234567890'), //Min 16 chars

    'secret' => env('MEANIFY_LARAVEL_OBFUSCATOR_SECRET', env('APP_KEY')),

    'log_to_db' => env('MEANIFY_LARAVEL_OBFUSCATOR_LOG_TO_DB', false),

    'table' => 'obfuscator_failures',
];
