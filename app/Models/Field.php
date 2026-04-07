<?php

declare(strict_types=1);

namespace Ppg\Models;
use PDO;
use PDOException;

/**
 * @property PDO $db Database connection
 */
class Field
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    /**
     * @param int $documentId
     * @return array<int, array<string, mixed>>
     * @throws PDOException
     */
    public function getFieldsByDocumentId(int $documentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM field WHERE document_id = ?");
        $stmt->execute([$documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $documentId
     * @param string $name
     * @param string $dataType
     * @return bool
     * @throws PDOException
     */
    public function createField(int $documentId, string $name, string $dataType): bool
    {
        $stmt = $this->db->prepare("INSERT INTO field (document_id, name, data_type) VALUES (?, ?, ?)");
        return $stmt->execute([$documentId, $name, $dataType]);
    }

    /**
     * @param int $fieldId
     * @return array<string, mixed>|null
     * @throws PDOException
     */
    public function getFieldById(int $fieldId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM field WHERE id = ?");
        $stmt->execute([$fieldId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }
}
?>