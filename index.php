<!DOCTYPE html>
<html>
<head>
    <title>Kursy walut</title>
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
    <h1>Kursy walut</h1>

    <?php
    require_once 'CurrencyConverter.php';

    // Sprawdzanie czy formularz został przesłany
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = $_POST['amount'];
        $sourceCurrency = $_POST['source_currency'];
        $targetCurrency = $_POST['target_currency'];

        // Walidacja danych wejściowych
        if (!empty($amount) && is_numeric($amount) && $amount > 0 && !empty($sourceCurrency) && !empty($targetCurrency)) {
            $converter = new CurrencyConverter();
            $convertedAmount = $converter->convertCurrency($amount, $sourceCurrency, $targetCurrency);

            if ($convertedAmount !== false) {
                // Zapisywanie wyników przewalutowań do bazy danych
                $converter->saveConversionResult($amount, $sourceCurrency, $targetCurrency, $convertedAmount);

                echo "<p>Przewalutowano $amount $sourceCurrency na $convertedAmount $targetCurrency.</p>";
            } else {
                echo "<p>Wystąpił błąd podczas przewalutowania.</p>";
            }
        } else {
            echo "<p>Wprowadź poprawne dane.</p>";
        }
    }
    ?>

    <h2>Przewalutowanie</h2>
    <form method="POST">
        Kwota: <input type="text" name="amount" required>
        Waluta źródłowa:
        <select name="source_currency" required>
            <option value="PLN">PLN</option>
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
        </select>
        Waluta docelowa:
        <select name="target_currency" required>
            <option value="PLN">PLN</option>
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
        </select>
        <input type="submit" value="Przewalutuj">
    </form>

    <h2>Ostatnie przewalutowania</h2>
    <?php
    // Wyświetlanie tabeli z ostatnimi wynikami przewalutowań
    $converter = new CurrencyConverter();
    $conversionResults = $converter->getConversionResults();

    if (!empty($conversionResults)) {
        echo '<table>';
        echo '<tr><th>Kwota</th><th>Waluta źródłowa</th><th>Waluta docelowa</th><th>Przewalutowana kwota</th></tr>';
        foreach ($conversionResults as $result) {
            echo '<tr>';
            echo "<td>{$result['amount']}</td>";
            echo "<td>{$result['source_currency']}</td>";
            echo "<td>{$result['target_currency']}</td>";
            echo "<td>{$result['converted_amount']}</td>";
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Brak wyników przewalutowań.</p>';
    }
    ?>
</body>
</html>
