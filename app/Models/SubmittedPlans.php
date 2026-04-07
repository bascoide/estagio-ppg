<?php
namespace Ppg\Models;

use PDO;

class SubmittedPlans
{
    private PDO $db;

    public function __construct()
    {
        $pdo = require __DIR__ . '/../../config/database.php';
        $this->db = $pdo;
    }

    public function createNewPlan(string $path): int
    {
        error_log("teste");
        $stmt = $this->db->prepare("INSERT INTO submitted_plans (path) VALUES (?)");
        $stmt->execute([$path]);
        $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->db->lastInsertId();
    }

    public function getPlanById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM submitted_plans WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function verifyPlan(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE submitted_plans SET verified = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}

?>