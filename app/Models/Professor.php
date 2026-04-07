<?php

namespace Ppg\Models;
use PDO;
use PDOException;

class Professor
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }
    public function getProfessorById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM professor WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Professor fetch failed: " . $e->getMessage());
            return null;
        }
    }

    public function getDocumentCreatedAtByProfessorId(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT professor_course.created_at FROM professor_course WHERE professor_course.professor_id = ? ");
            $stmt->execute([$id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Document creation date fetch failed: " . $e->getMessage());
            return null;
        }
    }

    public function getInternsByProfessorCourseAndYear(int $professorId, string $schoolYear, ?int $courseId = null): ?array
    {
        try {
            // Converta o formato do ano letivo "2024/2025" para anos de início e fim
            $years = explode('/', $schoolYear);
            $startYear = $years[0];
            $endYear = $years[1] ?? $startYear + 1; // Recoloca o próximo ano se não houver segundo ano

            $sql = "SELECT DISTINCT user.id, user.name as intern_name 
                    FROM user
                    INNER JOIN professor_course ON professor_course.intern_id = user.id
                    INNER JOIN course ON professor_course.course_id = course.id
                    INNER JOIN final_document ON final_document.user_id = user.id
                    WHERE professor_course.professor_id = :professor_id
                    AND (
                        (YEAR(professor_course.created_at) = :start_year AND MONTH(professor_course.created_at) >= 9) OR
                        (YEAR(professor_course.created_at) = :end_year AND MONTH(professor_course.created_at) < 9)
                    )
                    AND final_document.status = 'Validado'";

            $params = [
                'professor_id' => $professorId,
                'start_year' => $startYear,
                'end_year' => $endYear
            ];

            if ($courseId !== null) {
                $sql .= " AND professor_course.course_id = :course_id";
                $params['course_id'] = $courseId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Fetching interns failed: " . $e->getMessage());
            return null;
        }
    }
    public function getCoursesByProfessorId(int $professorId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT DISTINCT course.*  FROM course
                INNER JOIN professor_course ON professor_course.course_id = course.id
                WHERE professor_course.professor_id = ?");
            $stmt->execute([$professorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Courses fetch failed: " . $e->getMessage());
            return null;
        }
    }
    public function getProfessorByFilter(?string $name = null, ?int $courseId = null): array
    {
        try {
            $sql = "SELECT DISTINCT professor.* FROM professor";
            $conditions = [];
            $params = [];

            if ($courseId !== null) {
                $sql .= " INNER JOIN professor_course ON professor_course.professor_id = professor.id";
                $conditions[] = "professor_course.course_id = ?";
                $params[] = $courseId;
            }

            // Adiciona condição nome se providenciado
            if ($name !== null && $name !== '') {
                $conditions[] = "professor.name LIKE ?";
                $params[] = "%$name%";
            }

            // Combina condições
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log("Professor filter failed: " . $e->getMessage());
            return [];
        }
    }

    public function getAllProfessors(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM professor");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Professor fetch failed: " . $e->getMessage());
            return null;
        }
    }

    public function createProfessor(string $name): ?int
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO professor (name) VALUES (?)");
            $stmt->execute([$name]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Professor creation failed: " . $e->getMessage());
            return null;
        }
    }

    public function addProfessorCourseAndIntern(int $professorId, int $courseId, int $internId): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO professor_course (professor_id, course_id, intern_id) VALUES (?,?,?)");
            return $stmt->execute([$professorId, $courseId, $internId]);
        } catch (PDOException $e) {
            error_log("Professor-course-intern relation failed: " . $e->getMessage());
            return false;
        }
    }

    public function professorExists(string $name): ?int
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM professor WHERE name = ?");
            $stmt->execute([$name]);
            $id = $stmt->fetchColumn();
            return $id === false ? null : (int) $id;
        } catch (PDOException $e) {
            error_log("Professor existence check failed: " . $e->getMessage());
            return null;
        }
    }
    public function getProfessorNameByFinalDocumentId(int $finalDocumentId): ?string
    {
        try {
            $stmt = $this->db->prepare("SELECT value FROM field_value
                INNER JOIN field ON field.id = field_value.field_id
                WHERE final_document_id = ? AND field.name = 'Orientador'");
            $stmt->execute([$finalDocumentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['value'] : null;
        } catch (PDOException $e) {
            error_log("Professor name fetch failed: " . $e->getMessage());
            return null;
        }
    }

    public function getProfessorByCourseId(int $courseId) : ?array{
        try {
            $stmt = $this->db->prepare("SELECT DISTINCT professor.name FROM professor
                INNER JOIN professor_course ON
                professor.id = professor_course.professor_id
                WHERE professor_course.course_id = ?");
            $stmt->execute([$courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Document creation date fetch failed: " . $e->getMessage());
            return null;
        }

    }
}
