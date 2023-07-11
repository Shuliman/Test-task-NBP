<?php
require_once 'vendor/autoload.php';

use CurrencyConverter\ConversionResultRepository;
use CurrencyConverter\CurrencyConverter;
use CurrencyConverter\CurrencyDataProvider;
use CurrencyConverter\DatabaseConnection;
use CurrencyConverter\Models\ConversionResult;
use DI\ContainerBuilder;

//solves problems in the docker for end-to-end frontend and backend
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: *");

$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

$config = require 'config.php';

$container->set('config', $config);
$container->set(DatabaseConnection::class, DI\create(DatabaseConnection::class)->constructor(DI\get('config')));
$container->set(CurrencyDataProvider::class, DI\create(CurrencyDataProvider::class)->constructor(DI\get(DatabaseConnection::class)));
$container->set(CurrencyConverter::class, DI\create(CurrencyConverter::class)->constructor(DI\get(CurrencyDataProvider::class)));
$container->set(ConversionResultRepository::class, DI\create(ConversionResultRepository::class)->constructor(DI\get(DatabaseConnection::class)));

$currencyConverter = $container->get(CurrencyConverter::class);
$conversionResultRepository = $container->get(ConversionResultRepository::class);
$currencyDataProvider = $container->get(CurrencyDataProvider::class);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && filter_input(INPUT_GET, 'currencies', FILTER_VALIDATE_BOOLEAN)) {
        $response = [
            'currencies' => $currencyDataProvider->getCurrencies()
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
            $convertedAmount = $currencyConverter->convertCurrency($amount, $sourceCurrency, $targetCurrency);

            if ($convertedAmount !== false) {
                $result = new ConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount, date('Y-m-d H:i:s'));

                //Saving the result of the conversion
                $conversionResultRepository->saveConversionResult($result);
            }
        }
        //Get list of the latest conversion results
        $conversionResults = $conversionResultRepository->getConversionResults(5);

        //Return data in JSON format
        header('Content-Type: application/json');

        //Forming API response
        $response = [
            'conversionResults' => $conversionResults
        ];

        //Output the answer in JSON format
        echo json_encode($response);
    }
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());

    // Send HTTP 500 status code and end script
    http_response_code(500);
    echo json_encode(['error' => 'Something went wrong, please try again later.']);
    exit;
}
