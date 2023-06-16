<?php
return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'currency_converter',
        'username' => 'root',
        'password' => '',
        'tableName' => 'conversion_results',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    ]
];
?>