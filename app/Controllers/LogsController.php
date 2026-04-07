<?php
namespace Ppg\Controllers;

use Ppg\Models\Logs;

class LogsController
{
    public function index(): void
    {
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $itemsPerPage = 10;

        $actionType = $_GET["action_type"] ?? null;
        $loggedName = $_GET["logged_name"] ?? null;
        $date = $_GET["date"] ?? null;

        $offset = ($currentPage - 1) * $itemsPerPage;

        $totalRecords = (new Logs)->getTotalLogs($loggedName, $actionType, $date);

        $totalPages = ceil($totalRecords / $itemsPerPage);

        $logs = (new Logs)->getLogs($loggedName, $actionType, $date, $offset, $itemsPerPage);

        $startRecord = $offset + 1;
        $endRecord = min($offset + $itemsPerPage, $totalRecords);

        // Assegura que a página atual não excede o total de páginas
        if ($currentPage > $totalPages && $totalPages > 0) {
            header('Location: ?page=' . $totalPages);
            exit;
        }

        $loggedNames = (new Logs)->getLoggedUsers();

        require __DIR__ . '/../Views/adminDashboard/logs.php';
    }

    public function logAction(string $action): void
    {
        // ações permitidas:
        $allowedActions = [
            'create-account',
            'accept-document',
            'reject-document',
            'invalidate-document',
            'validate-document',
            'edit-document',
            'annul-document',
            'addition-document',
            'upload-document',
            'deactivation-document',
            'restore-document',
            'create-course',
            'delete-course',
            'edit-course',
            'deactivation-course'
        ];


        if (!in_array($action, $allowedActions)) {
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $adminName = $_SESSION['admin_name'] ?? 'Unknown Admin';

        $documentId = isset($_POST['final_document_id']) ? (int) $_POST['final_document_id'] : null;

        (new Logs)->createLogs($userId, $action, $adminName, $documentId);
    }
}

