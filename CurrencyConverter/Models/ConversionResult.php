<?php

namespace CurrencyConverter\Models;

class ConversionResult
{
    public $amount;
    public $sourceCurrency;
    public $targetCurrency;
    public $convertedAmount;
    public $date;

    public function __construct(float $amount, string $sourceCurrency, string $targetCurrency, float $convertedAmount, string $date)
    {
        $this->amount = $amount;
        $this->sourceCurrency = $sourceCurrency;
        $this->targetCurrency = $targetCurrency;
        $this->convertedAmount = $convertedAmount;
        $this->date = $date;
    }
}
