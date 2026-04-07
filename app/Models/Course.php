<?php
namespace Ppg\Models;

use PDO;

class Course
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    public function getCourseNameById(int $id): string {
        $stmt = $this->db->prepare("SELECT name FROM course WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function getCourseTypes(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT * FROM type_course");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function showCourses(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT course.*, type_course.name AS type_course_name FROM course
        INNER JOIN type_course ON course.type_course_id = type_course.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // o $isActive tem de ser string apesar de apenas receber 0 1 e null
    public function getFilteredCourses(string $courseName = '', string $isActive = null): array
    {
        $sql = "SELECT course.*, type_course.name AS type_course_name FROM course
        INNER JOIN type_course ON course.type_course_id = type_course.id
        WHERE 1=1";
        $params = [];

        if (!empty($courseName)) {
            $sql .= " AND course.name LIKE :name";
            $params[':name'] = "%$courseName%";
        }

        error_log("is_active: " . $isActive);
        error_log(gettype($isActive));
        if ($isActive !== null && $isActive !== '') {
            $sql .= " AND is_active = :is_active";
            $params[':is_active'] = $isActive;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT user.id as user_id,
                            course.id,
                            course.name,
                            course.type_course_id
                        FROM user
                        INNER JOIN course ON user.course_id = course.id
                        WHERE user.id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleCourseStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE course SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function editCourseName(int $id, string $newName): bool
    {
        $stmt = $this->db->prepare("UPDATE course SET name = :name WHERE id = :id");
        return $stmt->execute([
            ':name' => $newName,
            ':id' => $id
        ]);
    }

    public function addCourse(string $name, int $courseTypeId, bool $isActive): bool
    {
        $stmt = $this->db->prepare("INSERT INTO course (name, type_course_id, is_active) VALUES (:name, :type_course_id, :is_active)");
        return $stmt->execute([
            ':name' => $name,
            ':type_course_id' => $courseTypeId,
            ':is_active' => $isActive
        ]);
    }

    public function deleteCourse(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM course WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

}

?>
