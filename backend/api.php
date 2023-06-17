<?php

require_once 'CurrencyConverter/CurrencyConverter.php';
require_once 'CurrencyConverter/Models/ConversionResult.php';

//solves problems in the docker for end-to-end frontend and backend
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: *");

use CurrencyConverter\CurrencyConverter;
use CurrencyConverter\Models\ConversionResult;

$config = require 'config.php';

$converter = new CurrencyConverter($config);


if ($_SERVER['REQUEST_METHOD'] === 'GET' && filter_input(INPUT_GET, 'currencies', FILTER_VALIDATE_BOOLEAN)) {
    $response = [
        'currencies' => $converter->getCurrencies()
    ];

    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Validate and filter the input data
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $sourceCurrency = filter_input(INPUT_POST, 'source_currency', FILTER_SANITIZE_SPECIAL_CHARS);
    $targetCurrency = filter_input(INPUT_POST, 'target_currency', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($amount !== false && $sourceCurrency !== null && $targetCurrency !== null) {
        //Convert sum
        $convertedAmount = $converter->convertCurrency($amount, $sourceCurrency, $targetCurrency);

        if ($convertedAmount !== false) {
            $result = new ConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount, date('Y-m-d H:i:s'));

            //Saving the result of the conversion
            $converter->saveConversionResult($result);
        }
    }
    //Get list of the latest conversion results
    $conversionResults = $converter->getConversionResults(5);

    //Return data in JSON format
    header('Content-Type: application/json');

    //Forming API response
    $response = [
        'conversionResults' => $conversionResults
    ];

    //Output the answer in JSON format
    echo json_encode($response);
}
