<?php

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
        $this->connection = new PDO("mysql:host=$this->serverName;dbname=$this->database", $this->username, $this->password, $this->options);

        $this->currencies = $this->fetchCurrencies();
    }

    public function convertCurrency(float $amount, string $sourceCurrency, string $targetCurrency): float
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

    private function fetchCurrencies(): array
    {
        $exchangeRates = $this->fetchExchangeRates();

        if ($exchangeRates === false) {
            return [];
        }

        $currencies[] = 'PLN'; // Add PLN to the currencies array
        $currencies = array_keys($exchangeRates);

        return $currencies;
    }

    private function fetchExchangeRates(): array|false
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

    public function saveConversionResult(float $amount, string $sourceCurrency, string $targetCurrency, float $convertedAmount): void
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO conversion_results (amount, source_currency, target_currency, converted_amount, date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$amount, $sourceCurrency, $targetCurrency, $convertedAmount]);
        } catch (PDOException $e) {
            throw new Exception("Error when saving conversion results");
        }
    }

    public function getConversionResults(int $limit): array
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM conversion_results ORDER BY date DESC LIMIT :limit");
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
