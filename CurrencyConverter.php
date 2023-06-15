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

        // Convert source currency to PLN
        $sourceRate = $exchangeRates[$sourceCurrency];
        $plnAmount = $amount * $sourceRate;

        // Convert PLN to target currency
        $targetRate = $exchangeRates[$targetCurrency];
        $convertedAmount = $plnAmount / $targetRate;

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

        // Add Polish Zloty (PLN) to exchange rates
        $exchangeRates['PLN'] = 1.0;

        return $exchangeRates;
    }

    public function saveConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount)
    {
        $stmt = $this->pdo->prepare("INSERT INTO conversion_results (amount, source_currency, target_currency, converted_amount, date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$amount, $sourceCurrency, $targetCurrency, $convertedAmount]);
    }

    public function getConversionResults($limit)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM conversion_results ORDER BY date DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function getCurrencies()
    {
        return ['PLN', 'USD', 'EUR', 'GBP', 'UAH', 'AUD']; // Add more currencies here
    }
}
