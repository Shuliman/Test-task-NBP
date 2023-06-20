<?php

namespace CurrencyConverter;

use CurrencyConverter\Models\ConversionResult;
use PDO;
use PDOException;
use Exception;

class CurrencyConverter
{
    private $serverName;
    private $database;
    private $username;
    private $password;
    private $options;
    public $tableName;
    private $currencies;

    public $connection;

    public function __construct(array $config)
    {
        $this->serverName = $config['db']['host'];
        $this->database = $config['db']['dbname'];
        $this->username = $config['db']['username'];
        $this->password = $config['db']['password'];
        $this->options = $config['db']['options'];
        $this->tableName = $config['db']['tableName'];
        $this->connection = $this->makeConnection();

        $this->currencies = $this->fetchCurrencies();
    }

    private function makeConnection(): PDO
    {
        try {
            return new PDO("mysql:host=$this->serverName;port=3306;dbname=$this->database", $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function convertCurrency(float $amount, string $sourceCurrency, string $targetCurrency): float
    {
        try {
            $exchangeRates = $this->fetchExchangeRates();
    
            if (!isset($exchangeRates[$sourceCurrency]) || !isset($exchangeRates[$targetCurrency])) {
                throw new Exception('Currency not supported: ' . $sourceCurrency . ' or ' . $targetCurrency);
            }
    
            // Convert source currency to PLN
            $sourceRate = $exchangeRates[$sourceCurrency];
            $plnAmount = $amount * $sourceRate;
    
            // Convert PLN to target currency
            $targetRate = $exchangeRates[$targetCurrency];
            $convertedAmount = $plnAmount / $targetRate;
    
            return $convertedAmount;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    private function fetchCurrencies(): array
    {
        try {
            $exchangeRates = $this->fetchExchangeRates();
            $currencies = array_keys($exchangeRates);
    
            return $currencies;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    private function fetchExchangeRates(): array|false
    {
        $url = 'http://api.nbp.pl/api/exchangerates/tables/A?format=json';

        try {
            $response = file_get_contents($url);
        } catch (PDOException $e) {
            throw new Exception('Unable to retrieve data from the URL: ' . $url);
        }
    
        $data = json_decode($response, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg());
        }
    
        if (empty($data)) {
        throw new Exception('No data retrieved from the API');
        }

        $rates = $data[0]['rates'];

        $exchangeRates = [];

        foreach ($rates as $rate) {
            $currency = $rate['code'];
            $exchangeRates[$currency] = $rate['mid'];
        }

        // Add Polish Zloty (PLN) to exchange rates
        $exchangeRates['PLN'] = 1.0;

        return $exchangeRates;
    }

    public function saveConversionResult(ConversionResult $result): void
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO $this->tableName (amount, source_currency, target_currency, converted_amount, date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$result->amount, $result->sourceCurrency, $result->targetCurrency, $result->convertedAmount]);
        } catch (PDOException $e) {
            throw new Exception("Error when saving conversion results");
        }
    }

    public function getConversionResults(int $limit): array
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM $this->tableName ORDER BY date DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            throw new Exception("Error when getting conversion results");
        }
    }

    public function getCurrencies(): array
    {
        return $this->currencies;
    }

}
