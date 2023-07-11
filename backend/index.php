<?php

require_once 'CurrencyConverter/CurrencyConverterOld.php';
require_once 'CurrencyConverter/Models/ConversionResult.php';

use CurrencyConverter\CurrencyConverterOld;
use CurrencyConverter\Models\ConversionResult;;

$config = require 'config.php';

$converter = new \CurrencyConverterOld\CurrencyConverterOld($config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and filter input data
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $sourceCurrency = filter_input(INPUT_POST, 'source_currency', FILTER_SANITIZE_SPECIAL_CHARS);
    $targetCurrency = filter_input(INPUT_POST, 'target_currency', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($amount !== false && $sourceCurrency !== null && $targetCurrency !== null) {
        // Convert amount
        $convertedAmount = $converter->convertCurrency($amount, $sourceCurrency, $targetCurrency);

        if ($convertedAmount !== false) {
            // Create ConversionResult object
            $result = new ConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount, date('Y-m-d H:i:s'));

            // Save conversion result
            $converter->saveConversionResult($result);
        }
    }
}

// Get the list of latest conversion results
$conversionResults = $converter->getConversionResults(5);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Currency Converter</title>
    <style>
        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Currency Converter</h1>

    <h2>Convert Currency</h2>
    <form method="POST">
        <label for="amount">Amount:</label>
        <input type="text" name="amount" id="amount" required><br>

        <label for="source_currency">Source Currency:</label>
        <select name="source_currency" id="source_currency" required>
            <?php foreach ($converter->getCurrencies() as $currency): ?>
                <option value="<?php echo $currency; ?>"><?php echo $currency; ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="target_currency">Target Currency:</label>
        <select name="target_currency" id="target_currency" required>
            <?php foreach ($converter->getCurrencies() as $currency): ?>
                <option value="<?php echo $currency; ?>"><?php echo $currency; ?></option>
            <?php endforeach; ?>
        </select><br>

        <input type="submit" value="Convert">
    </form>

    <?php if (isset($convertedAmount)): ?>
        <?php if ($convertedAmount !== false): ?>
            <p>Converted Amount: <?php echo number_format($convertedAmount,4); ?></p>
        <?php else: ?>
            <p>Conversion failed. Please check your input.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Conversion Results</h2>
    <?php if (!empty($conversionResults)): ?>
        <table>
            <tr>
                <th>Amount</th>
                <th>Source Currency</th>
                <th>Target Currency</th>
                <th>Converted Amount</th>
                <th>Date</th>
            </tr>
            <?php foreach ($conversionResults as $result): ?>
                <tr>
                    <td><?php echo $result['amount']; ?></td>
                    <td><?php echo $result['source_currency']; ?></td>
                    <td><?php echo $result['target_currency']; ?></td>
                    <td><?php echo $result['converted_amount']; ?></td>
                    <td><?php echo $result['date']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No conversion results available.</p>
    <?php endif; ?>
</body>
</html>