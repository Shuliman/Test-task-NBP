<?php

class CurrencyConverter
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function convertCurrency($amount, $sourceCurrency, $targetCurrency)
    {
        $exchangeRates = $this->fetchExchangeRates();

        if ($exchangeRates === false) {
            return false;
        }

        if (!isset($exchangeRates[$sourceCurrency]) || !isset($exchangeRates[$targetCurrency])) {
            return false;
        }

        $sourceRate = $exchangeRates[$sourceCurrency];
        $targetRate = $exchangeRates[$targetCurrency];

        $convertedAmount = $amount * ($targetRate / $sourceRate);
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

        if (empty($data)) {
            return false;
        }

        $rates = $data[0]['rates'];

        $exchangeRates = [];

        foreach ($rates as $rate) {
            $currency = $rate['code'];
            $exchangeRates[$currency] = $rate['mid'];
        }

        return $exchangeRates;
    }

    public function saveConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount)
    {
        $stmt = $this->pdo->prepare("INSERT INTO conversion_results (amount, source_currency, target_currency, converted_amount, date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$amount, $sourceCurrency, $targetCurrency, $convertedAmount]);
    }

    public function getConversionResults()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM conversion_results ORDER BY date DESC LIMIT 10");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
