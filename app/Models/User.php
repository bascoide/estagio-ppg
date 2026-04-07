<?php

declare(strict_types=1);

namespace Ppg\Models;
use PDO;
use PDOException;

/**
 * @property PDO $db Database connection
 */
class User
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     * @throws PDOException
     */
    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    /**
     * @param string $search
     * @return array<int, array<string, mixed>>
     * @throws PDOException
     */
    public function getAllUsers(string $search = ''): array
    {
        if (!empty($search)) {
            $stmt = $this->db->prepare("SELECT * FROM user WHERE name LIKE :search ORDER BY name ASC");
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM user ORDER BY name ASC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $email
     * @return array<string, mixed>|null
     * @throws PDOException
     */
    public function findUser(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function verifyUser(string $email, string $verificationCode): bool
    {
        $stmt = $this->db->prepare("UPDATE user SET verified = 1 WHERE email = ? AND verification_code = ?");
        $stmt->execute([$email, $verificationCode]);
        return $stmt->rowCount() > 0;
    }

    public function deleteUnverifiedUser(
        string $email,
    ): bool {
        $stmt = $this->db->prepare("DELETE FROM user WHERE email = ? AND verified = 0");
        return $stmt->execute([$email]);
    }


    public function createUser(
        string $name,
        string $email,
        string $password,
        bool $admin = false,
        ?int $courseId = null,
        ?string $verificationCode = null,
        bool $verified = true
    ): bool {
        $stmt = $this->db->prepare("INSERT INTO user (email, name, password, admin, course_id, verification_code, verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$email, $name, $password, (int) $admin, $courseId, $verificationCode, (int) $verified]);
    }

    /**
     * @param string $search
     * @param string $date_filter
     * @param string $order_by
     * @param int $document_id
     * @return array<int, array<string, mixed>>
     * @throws PDOException
     */
    public function usersFiltredDocuments(
        string $search = '',
        string $date_filter = '',
        string $order_by = '',
        int $document_id,
        string $status = ''
    ): array {
        $query = "SELECT
                    user.id,
                    user.email,
                    final_document.id AS final_document_id,
                    final_document.created_at,
                    final_document.pdf_path,
                    document.id AS document_id,
                    final_document.status
                  FROM user
                  INNER JOIN final_document ON final_document.user_id = user.id
                  INNER JOIN document ON final_document.document_id = document.id
                  WHERE document.id = :document_id";

        $query .= match ($status) {
            'Pendente' => ' AND final_document.status = :status',
            'Aceite' => ' AND final_document.status = :status',
            'Recusado' => ' AND final_document.status = :status',
            'Inativo' => ' AND final_document.status = :status',
            default => ''
        };

        if (!empty($search)) {
            $query .= " AND user.email LIKE :search";
        }

        if (!empty($date_filter)) {
            $query .= " AND DATE(final_document.created_at) = :date_filter";
        }

        $query .= match ($order_by) {
            "date_newest" => " ORDER BY final_document.created_at DESC",
            "date_oldest" => " ORDER BY final_document.created_at ASC",
            default => ""
        };

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);

        if (!empty($status)) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        if (!empty($date_filter)) {
            $stmt->bindValue(':date_filter', $date_filter, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
