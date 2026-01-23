<?php
declare(strict_types=1);

return [
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=cisc;charset=utf8mb4',
        'user' => 'root',
        'pass' => 'buzby2997',
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
    'app' => [
        // If deployed in a subfolder, set e.g. '/myapp/public'
        'base_path' => '',
    ],
];