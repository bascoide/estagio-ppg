<?php

declare(strict_types=1);

namespace Ppg\Models;
use PDO;
use PDOException;

/**
 * @property PDO $db Database connection
 */
class President
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    public function getPresidentialEmails(): ?array {
        $stmt = $this->db->prepare("SELECT * FROM president_emails");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function createPresidentialEmail(string $presidentEmail): bool {
        $stmt = $this->db->prepare("INSERT IGNORE INTO president_emails (email) VALUES (?)");
        $result = $stmt->execute([$presidentEmail]);
        return $result;
    }

    public function createPresidentAproveRequest(int $finalDocumentId): bool {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        $stmt = $this->db->prepare("INSERT INTO president_validated_documents (uuid, final_document_id) VALUES (?, ?)");
        $result = $stmt->execute([$uuid, $finalDocumentId]);
        return $result;
    }

    public function getPresidentAproveRequest(int $finalDocumentId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM president_validated_documents WHERE final_document_id = ?");
        $stmt->execute([$finalDocumentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }
    public function presidentverifyDocument(int $finalDocumentId): bool {
        $stmt = $this->db->prepare("UPDATE president_validated_documents SET is_verified = 1 WHERE final_document_id = ?");
        $result = $stmt->execute([$finalDocumentId]);
        return $result;
    }

    public function getUserInfoByUuid(string $uuid): ?array {
        $stmt = $this->db->prepare("SELECT user.id AS user_id, user.email, final_document.document_id, final_document.plan_id, final_document.id As final_document_id
        FROM president_validated_documents
        INNER JOIN final_document ON president_validated_documents.final_document_id = final_document.id
        INNER JOIN user ON final_document.user_id = user.id
        WHERE uuid = ?");
        $stmt->execute([$uuid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function validatePresidentDocument(string $uuid): bool {
        $stmt = $this->db->prepare("UPDATE president_validated_documents SET is_validated = 1 WHERE uuid = ?");
        $result = $stmt->execute([$uuid]);
        return $result;
    }

    public function isDocumentValidated(string $uuid): bool {
        $stmt = $this->db->prepare("SELECT is_validated FROM president_validated_documents WHERE uuid = ?");
        $stmt->execute([$uuid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false && (bool)$result['is_validated'];
    }

    public function deletePresidentEmail(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM president_emails WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
?>
