<?php
namespace CurrencyConverter;
use CurrencyConverter\Models\ConversionResult;
use PDO;
use PDOException;
use Exception;
class ConversionResultRepository
{
    private $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function saveConversionResult(ConversionResult $result): void
    {
        try {
            $tableName = $this->databaseConnection->getTableName();

            $stmt = $this->databaseConnection->getConnection()->prepare("INSERT INTO $tableName (amount, source_currency, target_currency, converted_amount, date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$result->amount, $result->sourceCurrency, $result->targetCurrency, $result->convertedAmount]);
        } catch (PDOException $e) {
            throw new Exception("Error when saving conversion results");
        }
    }

    public function getConversionResults(int $limit): array
    {
        try {
            $tableName = $this->databaseConnection->getTableName();

            $stmt = $this->databaseConnection->getConnection()->prepare("SELECT * FROM $tableName ORDER BY date DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            throw new Exception("Error when getting conversion results");
        }
    }
}
