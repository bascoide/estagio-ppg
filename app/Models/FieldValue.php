<?php

declare(strict_types=1);

namespace Ppg\Models;
use PDO;
use PDOException;

class FieldValue
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    /**
     * @param int $finalDocumentId
     * @return array<int, array<string, mixed>>
     * @throws PDOException
     */
    public function getFieldValuesByFinalDocumentId(int $finalDocumentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM field_value WHERE final_document_id = ?");
        $stmt->execute([$finalDocumentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateFieldValue(int $fieldId, string $newFieldValue): void
    {
        $stmt = $this->db->prepare("UPDATE field_value SET value = ? WHERE field_id = ?");
        $stmt->execute([$newFieldValue, $fieldId,]);
    }

    /**
     * @param int $documentId
     * @param int $userId
     * @param array{
     *     field_ids: array<int, string>,
     *     field_values: array<int, string>,
     *     document_id?: string
     * } $formData
     * @param int $finalDocumentId
     * @return bool
     * @throws PDOException
     */
    public function createVariusFieldValues(
        int $documentId,
        int $userId,
        array $formData,
        int $finalDocumentId
    ): bool {
        if (empty($formData['field_ids']) || empty($formData['field_values'])) {
            return false;
        }

        // Combinar campos ID's com seus valores
        $fieldData = array_combine($formData['field_ids'], $formData['field_values']);
        if ($fieldData === false) {
            return false;
        }

        $sql = "INSERT INTO field_value (document_id, user_id, field_id, value, final_document_id) VALUES ";
        $placeholders = [];
        $params = [];
        $paramIndex = 0;

        foreach ($fieldData as $fieldId => $value) {
            $docParam = ":docId_" . $paramIndex;
            $userParam = ":userId_" . $paramIndex;
            $fieldParam = ":fieldId_" . $paramIndex;
            $valueParam = ":value_" . $paramIndex;

            $placeholders[] = "($docParam, $userParam, $fieldParam, $valueParam, :finalDocId)";

            $params[$docParam] = $documentId;
            $params[$userParam] = $userId;
            $params[$fieldParam] = (int) $fieldId;
            $params[$valueParam] = (string) $value;

            $paramIndex++;
        }

        if (empty($placeholders)) {
            return false;
        }

        $sql .= implode(", ", $placeholders);

        try {
            $stmt = $this->db->prepare($sql);

            // Atribuir final document ID uma vez que ele tem o mesmo para todas as linhas
            $stmt->bindValue(':finalDocId', $finalDocumentId, PDO::PARAM_INT);

            foreach ($params as $key => $val) {
                $stmt->bindValue(
                    $key,
                    $val,
                    is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }
}
?>