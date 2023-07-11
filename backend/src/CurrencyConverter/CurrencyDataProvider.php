<?php
namespace CurrencyConverter;
use PDOException;
use Exception;
class CurrencyDataProvider
{
    private $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }
    public function fetchExchangeRates(): array|false
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
    public function getCurrencies(): array
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
}
