<?php
declare(strict_types=1);

namespace Ppg\Models;
use PDO;

/**
 * @property PDO $db Database connection
 */
class Logs
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    public function createLogs(int $userId, string $action, string $name, int $finalDocumentId = null, int $editedFinalDocumentId = null): void
    {
        $stmt = $this->db->prepare("INSERT INTO logs (user_id, action, name, final_document_id)
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $name, $finalDocumentId]);
    }


    public function getLoggedUsers(): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT name FROM logs");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogs(?string $loggedName, ?string $actionType, ?string $date, int $offset, int $limit): array
    {
        $sql = "SELECT l.*, u.email
                FROM logs l
                JOIN user u ON l.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($loggedName) {
            $sql .= " AND l.name = :loggedName";
            $params[':loggedName'] = $loggedName;
        }

        if ($actionType) {
            $sql .= " AND l.action = :actionType";
            $params[':actionType'] = $actionType;
        }

        if ($date) {
            $sql .= " AND DATE(l.created_at) = :date";
            $params[':date'] = $date;
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalLogs(?string $loggedName, ?string $actionType, ?string $date): int
    {
        $sql = "SELECT COUNT(*)
            FROM logs l
            WHERE 1=1";
        $params = [];

        if ($loggedName) {
            $sql .= " AND l.name = ?";
            $params[] = $loggedName;
        }

        if ($actionType) {
            $sql .= " AND l.action = ?";
            $params[] = $actionType;
        }

        if ($date) {
            $sql .= " AND DATE(l.created_at) = ?";
            $params[] = $date;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

}
?>
