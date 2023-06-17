<?php
return [
    'db' => [
        'host' => '172.17.0.2',
        'dbname' => 'currency_converter',
        'username' => 'root',
        'password' => 'your_password',
        'tableName' => 'conversion_results',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    ]
];
?>