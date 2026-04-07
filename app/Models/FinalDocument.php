<?php
namespace Ppg\Models;
use PDO;
use PDOStatement;

class FinalDocument
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    public function totalPendingFinalDocuments(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM final_document WHERE status = 'Pendente' AND plan_id IS NOT NULL");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function totalNeedValidationFinalDocuments(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM final_document WHERE status = 'Por validar' AND plan_id IS NOT NULL");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function totalValidationFinalDocuments(string $startDate, string $endDate): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM final_document WHERE status = 'Validado' AND plan_id IS NOT NULL AND created_at BETWEEN ? AND ?");
        $stmt->execute([$startDate, $endDate]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function getFinalDocumentById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM final_document WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function getFinalDocumentsByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM final_document WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $userId
     * @param string $pdfPath
     * @param int $documentId
     * @return string
     * @throws \PDOException
     */
    public function createFinalDocument(int $userId, string $pdfPath, int $documentId, string $status, ?int $planId): string
    {
        $stmt = $this->db->prepare("INSERT INTO final_document (user_id, pdf_path, document_id, status, plan_id)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $pdfPath, $documentId, $status, $planId]);

        return $this->db->lastInsertId();
    }

    /**
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function viewDocumentsByUser(int $userId): array
    {
        $sql = "SELECT final_document.id as final_document_id,
        final_document.pdf_path as final_document_path,
        final_document.created_at,
        document.name,
        document.id as document_id
        FROM user
        INNER JOIN final_document ON user.id = final_document.user_id
        INNER JOIN document ON final_document.document_id = document.id
        WHERE user.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function viewAdditionByFinalDocumentId(int $finalDocumentId): array
    {
        $sql = "SELECT addition.id, 
            final_document.id as final_document_id,
            addition.name,
            addition.path as addition_path,
            addition.created_at
            FROM addition
            INNER JOIN final_document ON addition.final_document_id = final_document.id
            WHERE final_document.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$finalDocumentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFinalDocumentsByCourseId(int $courseId, string $schoolYearStart, string $schoolYearEnd): int
    {
        $sql = "SELECT COUNT(*) 
                FROM final_document 
                INNER JOIN user ON final_document.user_id = user.id 
                WHERE user.course_id = ? AND final_document.status = 'Validado'
                AND final_document.created_at BETWEEN ? AND ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId, $schoolYearStart, $schoolYearEnd]);
        return (int) $stmt->fetchColumn();
    }

    public function createAddition(int $finalDocumentId, string $name, string $path): string
    {
        $stmt = $this->db->prepare("INSERT INTO addition (final_document_id, name, path)
            VALUES (?, ?, ?)");
        $stmt->execute([$finalDocumentId, $name, $path]);

        return $this->db->lastInsertId();
    }

    /**
     * Get documents with status and pagination
     * @param string $status Document status to filter
     * @param int $offset Starting position
     * @param int $limit Number of records to return
     * @param string|null $startDate Optional start date filter
     * @param string|null $endDate Optional end date filter
     * @return array
     */
    public function getDocumentWithStatus(
        string $status, 
        int $offset = 0,
        int $limit = 10,
        string $startDate = null, 
        string $endDate = null
    ): array {
        $query = "SELECT *,
            user.name as name,
            user.email,
            document.name as document_name,
            document.id as document_id,
            final_document.id as final_document_id,
            final_document.created_at as final_document_created_at,
            document.type as document_type,
            submitted_plans.id as plan_id,
            submitted_plans.path as plan_path,
            submitted_plans.verified as plan_is_verified
            FROM final_document
            INNER JOIN submitted_plans ON submitted_plans.id = plan_id
            INNER JOIN user ON final_document.user_id = user.id
            INNER JOIN document ON final_document.document_id = document.id
            WHERE status = :status";

        if ($startDate && $endDate) {
            $query .= " AND final_document.created_at BETWEEN :start_date AND :end_date";
        }

        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($startDate && $endDate) {
            $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
            $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getValidatedDocumentsDate(): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT DATE(created_at) FROM final_document WHERE status = 'Validado'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param int $user_id
     * @param string $search
     * @param string $date_filter
     * @param string $order_by
     * @return array<int, array<string, mixed>>
     */
    public function documentsFilledByUser(
        int $user_id,
        string $search = '',
        string $date_filter = '',
        string $order_by = '',
        string $status = ''
    ): array {
        $query = "SELECT
        document.id AS document_id,
        document.name AS document_name,
        document.type AS document_type,
        final_document.id AS final_document_id,
        final_document.created_at,
        final_document.pdf_path,
        final_document.status
        FROM final_document
        INNER JOIN document ON final_document.document_id = document.id
        WHERE final_document.user_id = :user_id";

        $query .= match ($status) {
            'Pendente' => ' AND final_document.status = :status',
            'Aceite' => ' AND final_document.status = :status',
            'Recusado' => ' AND final_document.status = :status',
            'Por validar' => ' AND final_document.status = :status',
            'Validado' => ' AND final_document.status = :status',
            'Invalidado' => ' AND final_document.status = :status',
            'Inativo' => ' AND final_document.status = :status',
            default => ''
        };

        if (!empty($search)) {
            $query .= " AND document.name LIKE :search";
        }

        if (!empty($date_filter)) {
            $query .= " AND DATE(final_document.created_at) = :date_filter";
        }

        $query .= match ($order_by) {
            'date_newest' => ' ORDER BY final_document.created_at DESC',
            'date_oldest' => ' ORDER BY final_document.created_at ASC',
            default => ''
        };

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

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

    /**
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE final_document SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        return $stmt->rowCount() > 0;
    }

    public function getDocumentStatusTeacher(string $teacherName): array
    {
        $currentYear = date('Y');

        if (date('n') >= 9) {
            $schoolYearStart = $currentYear . '-09-01';
            $schoolYearEnd = ($currentYear + 1) . '-08-31';
        } else {
            $schoolYearStart = ($currentYear - 1) . '-09-01';
            $schoolYearEnd = $currentYear . '-08-31';
        }

        $stmt = $this->db->prepare("SELECT
            final_document.status,
            final_document.created_at AS delivered_at,
            user.name AS student_name,
            user.email
            FROM final_document
            INNER JOIN field_value ON
            field_value.final_document_id = final_document.id
            INNER JOIN field ON
            field.id = field_value.field_id
            INNER JOIN user ON
            user.id = final_document.user_id
            WHERE (final_document.status = 'Aceite' OR final_document.status = 'Validado') AND
            field.data_type = 'professor' AND
            field_value.value = ? AND
            final_document.created_at BETWEEN ? AND ?");

        $stmt->execute([$teacherName, $schoolYearStart, $schoolYearEnd]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>