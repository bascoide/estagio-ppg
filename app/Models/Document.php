<?php

declare(strict_types=1);

namespace Ppg\Models;
use PDO;
use PDOException;

/**
 * @property PDO $db Database connection
 */
class Document
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    /**
     * @param string $search
     * @return array<int, array<string, mixed>>
     * @throws PDOException
     */
    public function getAllDocuments(string $search = ''): array
    {
        if (!empty($search)) {
            $stmt = $this->db->prepare("SELECT * FROM document WHERE name LIKE :search");
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM document");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     * @throws PDOException
     */
    public function getDocumentById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM document WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    /**
     * @param string $docxPath
     * @param string $name
     * @param string $type
     * @param array<int> $typeCourseId
     * @return int
     * @throws PDOException
     */
    public function createDocument(string $docxPath, string $name, string $type, array $typeCourseId): int
    {
        $stmt = $this->db->prepare("INSERT INTO document (docx_path, name, type, is_active) VALUES (?, ?, ?, TRUE)");
        $stmt->execute([$docxPath, $name, $type]);

        $documentId = (int) $this->db->lastInsertId();

        $stmt = $this->db->prepare("INSERT INTO document_type_course (document_id, type_course_id) VALUES (?, ?)");
        foreach ($typeCourseId as $courseId) {
            $stmt->execute([$documentId, (int) $courseId]);
        }

        return $documentId;
    }

    /**
     * Pegar todos os documentos associados com um curso ID específico
     *
     * @param int $courseId
     * @return array<int, array<string, mixed>>
     * @throws PDOException
     */
    public function getDocumentsByTypeCourseId(int $typeCourseId): array
    {
        $sql = "SELECT d.*
                FROM document d
                INNER JOIN document_type_course dtc ON d.id = dtc.document_id
                WHERE dtc.type_course_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$typeCourseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deactivateDocument(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE document SET is_active = FALSE WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function activateDocument(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE document SET is_active = TRUE WHERE id = ?");
        $stmt->execute([$id]);
    }
}
?>
