<?php

declare(strict_types=1);

namespace Ppg\Controllers;
use PDO;
use PDOException;
use Exception;
use Ppg\Models\Document;
use Ppg\Models\User;
use Ppg\Models\FinalDocument;
use Ppg\Models\FieldValue;
use Ppg\Models\Field;
use Ppg\Models\President;
use Ppg\Models\Course;
use Ppg\Models\Professor;
use Ppg\Controllers\FormController;
use Ppg\Controllers\EmailController;
use DateTime;
use Ppg\Controllers\LogsController;

class AdminPanelController
{
    private function goToPreviousPage(): void
    {
        $previousPage = $_SESSION["previous_page"];
        // Remove index.php from URL if present
        $previousPage = str_replace('index.php/', '', $previousPage);

        header("Location: " . $previousPage);
        exit();
    }

    /**
     * @throws Exception
     */
    public function createAdmin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Email e senha são obrigatórios!";
                header('Location: create-admin');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            try {
                $existingUser = $userModel->findUser($email);

                if ($existingUser !== null) {
                    $_SESSION['error'] = "Email já está registado!";
                    header('Location: create-admin');
                    return;
                }

                $result = $userModel->createUser('Admin', $email, $hashedPassword, true);
                if (!$result) {
                    throw new Exception("Falha ao criar utilizador.");
                }

                (new LogsController)->logAction('create-account');
                $_SESSION['message'] = "Utilizador criado com sucesso!";
                header('Location: create-admin');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao criar utilizador: " . $e->getMessage();
                header('Location: create-admin');
            }
        }
        require __DIR__ . '/../Views/adminDashboard/createAdmin.php';
    }

    /**
     * @throws Exception
     */
    public function showUsers(): void
    {
        $userModel = new User();
        $search = $_GET['search'] ?? '';
        $users = $userModel->getAllUsers((string) $search);
        require __DIR__ . '/../Views/adminDashboard/showUsers.php';
    }

    /**
     * @throws Exception
     */
    public function showDocuments(): void
    {
        $documentModel = new Document();
        $search = $_GET['search'] ?? '';
        $documents = $documentModel->getAllDocuments((string) $search);
        require __DIR__ . '/../Views/adminDashboard/showDocuments.php';
    }

    /**
     * @throws Exception
     */
    public function viewFinalDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $finalDocumentId = (int) $_GET['final_document_id'] ?? null;
            $documentId = (int) $_GET['document_id'] ?? null;

            if (
                !$finalDocumentId || !$documentId ||
                !filter_var($finalDocumentId, FILTER_VALIDATE_INT) ||
                !filter_var($documentId, FILTER_VALIDATE_INT)
            ) {
                $_SESSION['error'] = "ID's inválidos";
                header('Location: show-documents');
                exit();
            }

            $fieldValuesModel = new FieldValue();
            $fieldModel = new Field();
            $teacherModel = new Professor();

            $finalDocumentModel = new FinalDocument();
            $status = $finalDocumentModel->getFinalDocumentById($finalDocumentId)['status'];
            $userId = $finalDocumentModel->getFinalDocumentById($finalDocumentId)['user_id'];
            $planId = $finalDocumentModel->getFinalDocumentById($finalDocumentId)['plan_id'];

            $teachers = $teacherModel->getAllProfessors();

            $fieldNames = $fieldModel->getFieldsByDocumentId($documentId);
            $fieldValues = $fieldValuesModel->getFieldValuesByFinalDocumentId($finalDocumentId);

            require __DIR__ . '/../Views/adminDashboard/viewUserDocument.php';
        }
    }

    /**
     * @throws Exception
     */
    public function viewUserDocuments(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = (int) $_GET['user_id'] ?? null;

            if (!$userId || !filter_var($userId, FILTER_VALIDATE_INT)) {
                $_SESSION['error'] = "ID do utilizador inválido";
                header('Location: show-users');
                exit();
            }

            $search = $_GET['search'] ?? '';
            $date_filter = $_GET['date_filter'] ?? '';
            $order_by = $_GET['order_by'] ?? '';
            $status = $_GET['status'] ?? '';

            $finalDocumentModel = new FinalDocument();
            $userModel = new User();

            $userName = $userModel->getUserById($userId)['name'];
            $documents = $finalDocumentModel->documentsFilledByUser(
                search: $search,
                date_filter: $date_filter,
                order_by: $order_by,
                user_id: (int) $userId,
                status: $status
            );

            require __DIR__ . '/../Views/adminDashboard/viewDocumentsFromUser.php';
        }
    }

    public function viewPendingDocuments(): void
    {
        $finalDocumentModel = new FinalDocument();
        
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $itemsPerPage = 10;
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        $documents = $finalDocumentModel->getDocumentWithStatus(
            'Pendente',
            $offset,
            $itemsPerPage
        );
        
        $totalRecords = $finalDocumentModel->totalPendingFinalDocuments();
        $totalPages = ceil($totalRecords / $itemsPerPage);
        
        $startRecord = $offset + 1;
        $endRecord = min($offset + $itemsPerPage, $totalRecords);
        
        // Assegura que a página atual não excede o total de páginas
        if ($currentPage > $totalPages && $totalPages > 0) {
            header('Location: ?page=' . $totalPages);
            exit;
        }
        
        require __DIR__ . '/../Views/adminDashboard/viewPendingDocuments.php';
    }

    public function viewNeedValidationDocuments()
    {
        $finalDocumentModel = new FinalDocument();

        $documents = $finalDocumentModel->getDocumentWithStatus('Por validar');
        $presidencialEmails = (new President)->getPresidentialEmails();

        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $itemsPerPage = 10;

        $offset = ($currentPage - 1) * $itemsPerPage;

        $totalRecords = (new FinalDocument)->totalNeedValidationFinalDocuments();

        $totalPages = ceil($totalRecords / $itemsPerPage);

        $startRecord = $offset + 1;
        $endRecord = min($offset + $itemsPerPage, $totalRecords);

        // Assegura que a página atual não excede o total de páginas
        if ($currentPage > $totalPages && $totalPages > 0) {
            header('Location: ?page=' . $totalPages);
            exit;
        }

        require __DIR__ . '/../Views/adminDashboard/viewNeedValidationDocuments.php';
    }

    public function viewValidationDocuments(): void
    {
        $finalDocumentModel = new FinalDocument();
        
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $itemsPerPage = 10;
        $offset = ($currentPage - 1) * $itemsPerPage;

        if ($_GET['school_year'] ?? null) {
            $schoolYear = $_GET['school_year'];
            $yearParts = explode('/', $schoolYear);
            if (count($yearParts) !== 2 || !is_numeric($yearParts[0]) || !is_numeric($yearParts[1])) {
                $_SESSION['error'] = "Ano escolar inválido";
                header('Location: view-validation-documents');
                exit();
            }
            $schoolYearStart = $yearParts[0] . '-09-01';
            $schoolYearEnd = $yearParts[1] . '-08-31';
            $protocolCount = $finalDocumentModel->countFinalDocumentsByCourseId(
                (int) $_GET['course_id'], 
                $schoolYearStart, 
                $schoolYearEnd
            );
        } 
        else if ($_GET['civil_year'] ?? null) {
            $civilYear = $_GET['civil_year'];
            $civilYearStart = $civilYear . "-01-01";
            $civilYearEnd = $civilYear . "-12-31";
            $protocolCount = $finalDocumentModel->countFinalDocumentsByCourseId(
                (int) $_GET['course_id'], 
                $civilYearStart, 
                $civilYearEnd
            );
        }

        if (isset($_GET['select_school_year'])) {
            $selectSchoolYear = $_GET['select_school_year'];
            $yearSelectParts = explode('/', $selectSchoolYear);
            if (count($yearSelectParts) !== 2 || !is_numeric($yearSelectParts[0]) || !is_numeric($yearSelectParts[1])) {
                $_SESSION['error'] = "Ano escolar inválido";
                header('Location: view-validation-documents');
                exit();
            }
            $startDate = $yearSelectParts[0] . '-09-01';
            $endDate = $yearSelectParts[1] . '-08-31';
        } else {
            $currentYear = date('Y');
            if (date('n') >= 8) {
                $startDate = $currentYear . '-09-01';
                $endDate = ($currentYear + 1) . '-08-31';
            } else {
                $startDate = ($currentYear - 1) . '-09-01';
                $endDate = $currentYear . '-08-31';
            }
        }

        $documents = $finalDocumentModel->getDocumentWithStatus(
            'Validado',
            $offset,
            $itemsPerPage,
            $startDate,
            $endDate
        );

        $documentsDate = array_reverse($finalDocumentModel->getValidatedDocumentsDate(), false);

        $schoolYears = [];
        $civilYears = [];

        foreach ($documentsDate as $date) {
            $documentDate = new DateTime($date);
            $civilYears[] = $documentDate->format('Y');

            if ($documentDate->format('m') >= 8) {
                $schoolYearStart = $documentDate->format('Y');
                $schoolYearEnd = $documentDate->format('Y') + 1;
            } else {
                $schoolYearStart = $documentDate->format('Y') - 1;
                $schoolYearEnd = $documentDate->format('Y');
            }
            $schoolYears[] = "$schoolYearStart/$schoolYearEnd";
        }

        $civilYears = array_unique($civilYears);
        $schoolYears = array_unique($schoolYears);

        $courseModel = new Course();
        $courses = $courseModel->showCourses();

        $totalRecords = $finalDocumentModel->totalValidationFinalDocuments($startDate, $endDate);
        $totalPages = ceil($totalRecords / $itemsPerPage);
        $startRecord = $offset + 1;
        $endRecord = min($offset + $itemsPerPage, $totalRecords);

        // Assegura que a página atual não excede o total de páginas
        if ($currentPage > $totalPages && $totalPages > 0) {
            header('Location: ?page=' . $totalPages);
            exit;
        }

        require __DIR__ . '/../Views/adminDashboard/viewValidationDocuments.php';
    }

    public function viewDocumentation(): void
    {
        require __DIR__ . '/../Views/adminDashboard/documentation.php';
    }

    public function viewAdditionDocuments(): void
    {
        try {
            if (isset($_FILES['documentFile'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/submittedAdditions/';

                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new Exception("Falha ao criar diretório de upload");
                    }
                }

                $fileType = mime_content_type($_FILES['documentFile']['tmp_name']);
                if ($fileType !== 'application/pdf') {
                    throw new Exception("Apenas arquivos PDF são permitidos!");
                }

                $extension = pathinfo($_FILES['documentFile']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('doc_', true) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;

                if (!move_uploaded_file($_FILES['documentFile']['tmp_name'], $targetPath)) {
                    throw new Exception("Erro ao salvar o arquivo.");
                }

                $filePath = '/uploads/submittedAdditions/' . $fileName;
                $finalDocumentId = (int) ($_GET['final_document_id'] ?? 0);
                $documentName = trim($_POST['name'] ?? '');

                if ($finalDocumentId <= 0) {
                    throw new Exception("ID do documento inválido");
                }

                if (empty($documentName)) {
                    throw new Exception("O nome do documento é obrigatório");
                }

                (new FinalDocument())->createAddition(
                    $finalDocumentId,
                    $documentName,
                    $filePath
                );

                $_POST["final_document_id"] = $finalDocumentId;
                (new LogsController)->logAction('addition-document');
                $_SESSION['success_message'] = "Aditamento adicionado com sucesso!";
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            }

            $finalDocumentId = (int) ($_GET['final_document_id'] ?? 0);
            if ($finalDocumentId <= 0) {
                throw new Exception("ID do documento inválido");
            }

            $finalDocumentModel = new FinalDocument();
            $additions = $finalDocumentModel->viewAdditionByFinalDocumentId($finalDocumentId);

            require __DIR__ . '/../Views/adminDashboard/additionDocument.php';

        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    // Editar
    public function cancelFinalDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['status'])) {
            $_SESSION['error'] = "Método inválido";
            $this->goToPreviousPage();
            exit();
        }

        try {
            $finalDocumentId = (int) $_POST['final_document_id'];
            $status = $_POST['status'] ?? null;

            if ($status !== 'Inativo') {
                throw new Exception("Status inválido");
            }

            $finalDocumentModel = new FinalDocument();
            $currentDocument = $finalDocumentModel->getFinalDocumentById($finalDocumentId);
            $userId = $currentDocument['user_id'];
            $userEmail = (new User())->getUserById($userId)['email'];

            // Atualiza o status do documento
            $finalDocumentModel->updateStatus($finalDocumentId, $status);

            // Envia email de cancelamento
            $this->sendStatusEmail($userEmail, $finalDocumentId, 'cancelled');

            (new LogsController)->logAction('anull-document');
            $_SESSION['message'] = "Documento cancelado com sucesso!";

            $this->goToPreviousPage();
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->goToPreviousPage();
            exit();
        }
    }

    public function editFinalDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['fields'])) {
            $_SESSION['error'] = "Método inválido";
            $this->goToPreviousPage();
            exit();
        }

        try {
            $finalDocumentId = (int) $_POST['final_document_id'];
            $fields = $_POST['fields'];
            $fieldNames = $_POST['field_names'] ?? [];
            $status = $_POST['status'] ?? null;

            $finalDocumentModel = new FinalDocument();
            $currentDocument = $finalDocumentModel->getFinalDocumentById($finalDocumentId);
            $userId = $currentDocument['user_id'];
            $userEmail = (new User())->getUserById($userId)['email'];

            $fieldValueModel = new FieldValue();
            $fieldValues = $fieldValueModel->getFieldValuesByFinalDocumentId($finalDocumentId);

            $formatedFieldValues = [];
            foreach ($fieldValues as $field) {
                $formatedFieldValues[$field['field_id']] = $field['value'];
            }

            if ($formatedFieldValues === $fields) {
                // Apenas mudança de estados - Atualiza sem criar nova versão
                $finalDocumentModel->updateStatus($finalDocumentId, $status);

                // rejectedFields
                // [$fieldName -> fieldValue,] etc
                if ($status === "Recusado" || $status === "Aceite") {
                    $rejectionReason = $_POST['rejection_reason'] ?? '';
                    $this->sendStatusEmail(
                        $userEmail,
                        $finalDocumentId,
                        $status === "Recusado" ? 'rejected' : 'accepted',
                        $rejectionReason,
                        $rejectedFields = $_POST['rejected_fields'] ?? []
                    );

                    if ($status === "Aceite") {
                        $professorModel = new Professor();
                        $courseModel = new Course();

                        $courses = $courseModel->getCourseByUserId($userId);
                        if (empty($courses)) {
                            throw new Exception("Curso não encontrado para o utilizador");
                        }
                        $courseId = $courses[0]['id'];

                        $professorName = $professorModel->getProfessorNameByFinalDocumentId($finalDocumentId);
                        if (!$professorName) {
                            throw new Exception("Nome do orientador não encontrado");
                        }

                        $professorId = $professorModel->professorExists($professorName);
                        if (!$professorId) {
                            $professorId = $professorModel->createProfessor($professorName);
                        }

                        if (!$professorModel->addProfessorCourseAndIntern($professorId, $courseId, $userId)) {
                            throw new Exception("Falha ao associar orientador");
                        }
                    }
                }


                (new LogsController)->logAction('edit-document');
                $_SESSION['message'] = "Status atualizado com sucesso!";
            } else {
                // Campos alterados - criar nova versão
                $newFinalDocumentId = $this->adminEditDocument(
                    $finalDocumentId,
                    $fields,
                    $fieldNames
                );

                // Atualiza estados do novo documento se necessário
                if ($status) {
                    $finalDocumentModel->updateStatus($newFinalDocumentId, $status);

                    if ($status === "Recusado" || $status === "Aceite") {
                        $rejectionReason = $_POST['rejection_reason'] ?? '';
                        $this->sendStatusEmail(
                            $userEmail,
                            $newFinalDocumentId,
                            $status === "Recusado" ? 'rejected' : 'accepted',
                            $rejectionReason
                        );
                    }
                }

                (new LogsController)->logAction('edit-document');
                $_SESSION['message'] = "Documento atualizado com sucesso!";
            }
            $this->goToPreviousPage();
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->goToPreviousPage();
        }
    }
    /**
     * Private method to send status emails
     */
    private function sendStatusEmail(string $userEmail, int $documentId, string $type, string $rejectionReason = '', array $rejectedFields = []): void
    {
        $smtpConfig = require __DIR__ . '/../../config/email.php';
        $emailController = new EmailController($smtpConfig);

        switch ($type) {
            case 'rejected':
                $emailController->sendRejectedEmail($userEmail, $documentId, $rejectionReason, $rejectedFields);
                break;
            case 'accepted':
                $emailController->sendAcceptedEmail($userEmail, $documentId);
                break;
            case 'cancelled':
                $emailController->sendCancelledEmail($userEmail, $documentId);
                break;
        }
    }
    /**
     * Private method to handle admin edits (takes arguments)
     * @param int $finalDocumentId
     * @param array $fields
     * @param array $fieldNames
     * @throws Exception
     */
    private function adminEditDocument(int $finalDocumentId, array $fields, array $fieldNames): int
    {
        $finalDocumentModel = new FinalDocument();
        $currentDocument = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        if (!$currentDocument) {
            throw new Exception("Documento não encontrado");
        }

        $submittedData = [
            'field_ids' => array_keys($fields),
            'field_names' => $fieldNames,
            'field_values' => $fields
        ];

        // Marca o documento antigo como inativo
        $finalDocumentModel->updateStatus($finalDocumentId, 'Inativo');

        $formController = new FormController();
        // Cria nova versão do documento
        $newDocumentId = $formController->createFinalDocument(
            $currentDocument['document_id'],
            $currentDocument['user_id'],
            $submittedData,
            $currentDocument['plan_id']
        );

        if (!$newDocumentId) {
            throw new Exception("Falha ao criar nova versão do documento");
        }

        // Salva os valores dos campos do novo documento
        $fieldValueModel = new FieldValue();
        if (
            !$fieldValueModel->createVariusFieldValues(
                $currentDocument['document_id'],
                $currentDocument['user_id'],
                $submittedData,
                $newDocumentId
            )
        ) {
            throw new Exception("Falha ao salvar as alterações");
        }
        return $newDocumentId;
    }
}
?>
