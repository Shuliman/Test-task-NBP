<?php
namespace CurrencyConverter;
use Exception;
class CurrencyConverter
{
    private $currencyDataProvider;
    public function __construct(CurrencyDataProvider $currencyDataProvider)
    {
        $this->currencyDataProvider = $currencyDataProvider;
    }

    public function convertCurrency(float $amount, string $sourceCurrency, string $targetCurrency): float
    {
        try {
            $exchangeRates = $this->currencyDataProvider->fetchExchangeRates();
    
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

}
