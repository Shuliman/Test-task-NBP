<?php

class CurrencyConverter
{
    private $db;

    public function __construct()
    {
        // Konfiguracja połączenia z bazą danych
        $dbHost = 'localhost';
        $dbUser = 'username';
        $dbPass = 'password';
        $dbName = 'currency_converter';

        $this->db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function convertCurrency($amount, $sourceCurrency, $targetCurrency)
    {
        // Pobieranie kursów walut z API NBP
        $exchangeRates = $this->fetchExchangeRates();

        if ($exchangeRates === false) {
            return false;
        }

        // Sprawdzanie czy waluty istnieją w kursach walutowych
        if (!isset($exchangeRates[$sourceCurrency]) || !isset($exchangeRates[$targetCurrency])) {
            return false;
        }

        // Przewalutowanie kwoty
        $sourceRate = $exchangeRates[$sourceCurrency];
        $targetRate = $exchangeRates[$targetCurrency];
        $convertedAmount = $amount * ($targetRate / $sourceRate);
        $convertedAmount = round($convertedAmount, 2);

        return $convertedAmount;
    }

    private function fetchExchangeRates()
    {
        $url = 'http://api.nbp.pl/api/exchangerates/tables/A?format=json';
        $response = file_get_contents($url);

        if ($response === false) {
            return false;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || empty($data)) {
            return false;
        }

        $rates = [];

        foreach ($data[0]['rates'] as $rate) {
            $rates[$rate['code']] = $rate['mid'];
        }

        return $rates;
    }

    public function saveConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount)
    {
        $query = 'INSERT INTO conversions (amount, source_currency, target_currency, converted_amount) VALUES (:amount, :source_currency, :target_currency, :converted_amount)';
        $statement = $this->db->prepare($query);
        $statement->bindParam(':amount', $amount, PDO::PARAM_STR);
        $statement->bindParam(':source_currency', $sourceCurrency, PDO::PARAM_STR);
        $statement->bindParam(':target_currency', $targetCurrency, PDO::PARAM_STR);
        $statement->bindParam(':converted_amount', $convertedAmount, PDO::PARAM_STR);
        $statement->execute();
    }

    public function getConversionResults()
    {
        $query = 'SELECT * FROM conversions ORDER BY id DESC LIMIT 10';
        $statement = $this->db->prepare($query);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
