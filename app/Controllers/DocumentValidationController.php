<?php
namespace Ppg\Controllers;

use Ppg\Models\FinalDocument;
use Ppg\Controllers\EmailController;
use Ppg\Models\President;

use Exception;
use Ppg\Controllers\LogsController;

class DocumentValidationController
{
    private function goToPreviousPage(): void
    {
        $previousPage = $_SESSION["previous_page"];
        $previousPage = str_replace('index.php/', '', $previousPage);

        header("Location: " . $previousPage);
        exit();
    }

    public function presidentValidationPage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_GET['uuid'])) {
                // No UUID provided, show regular form
                require __DIR__ . '/../Views/adminDashboard/presidentUploadFinalDocument.php';
                return;
            }

            $uuid = $_GET['uuid'];
            $isVerified = (new President)->isDocumentValidated($uuid);

            if ($isVerified) {
                $_SESSION['error'] = "O documento já foi validado.";
                header('Location: /president-upload-final-document-form');
                exit();
            }
            require __DIR__ . '/../Views/adminDashboard/presidentUploadFinalDocument.php';
        } else {
            require __DIR__ . '/../Views/adminDashboard/presidentUploadFinalDocument.php';
        }
    }

    public function validateDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $finalDocumentId = $_POST['final_document_id'];
            $presidencialEmail = $_POST['presidencial_email'];
            $userId = $_POST['user_id'];
            $adminName = $_SESSION["admin_name"];


            $smtpConfig = require __DIR__ . '/../../config/email.php';
            $emailController = new EmailController($smtpConfig);
            (new President)->createPresidentAproveRequest($finalDocumentId);
            (new President)->createPresidentialEmail($presidencialEmail);
            (new FinalDocument)->updateStatus($finalDocumentId, 'Inativo');
            $emailController->sendPresidentialValidatonEmail($presidencialEmail, $finalDocumentId, $userId, $adminName);

            (new LogsController)->logAction('validate-document');
            $_SESSION['message'] = "Documento validado com sucesso!";
            header('Location: /need-validation-documents');
            exit();
        }

    }

    public function presidentFinalDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erro no upload do arquivo");
                }

                $fileType = mime_content_type($_FILES['document']['tmp_name']);
                if ($fileType !== 'application/pdf') {
                    throw new Exception("Arquivo deve ser PDF");
                }

                $uploadDir = __DIR__ . '/../../public/uploads/generated_docs';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = uniqid('president_doc_') . '.pdf';
                $pdfPath = $uploadDir . '/' . $filename;

                if (!move_uploaded_file($_FILES['document']['tmp_name'], $pdfPath)) {
                    throw new Exception("Erro ao salvar arquivo");
                }

                $uuid = $_POST['verified_uuid'];
                $userInfo = (new President)->getUserInfoByUuid($uuid);

                $userId = $userInfo['user_id'];
                $documentId = $userInfo['document_id'];
                $finalDocumentId = $userInfo['final_document_id'];
                $userEmail = $userInfo['email'];
                $planId = $userInfo['plan_id'];
                $status = 'Validado';

                $smtpConfig = require __DIR__ . '/../../config/email.php';
                $emailController = new EmailController($smtpConfig);
                $emailController->sendAcceptedValidationEmail($userEmail, $finalDocumentId);

                $finalDocumentModel = new FinalDocument();
                $result = $finalDocumentModel->createFinalDocument($userId, $filename, $documentId, $status, $planId);

                if (!$result) {
                    throw new Exception("Falha ao criar final document record");
                }

                $_SESSION['message'] = "Documento finalizado com sucesso!";

                (new President)->validatePresidentDocument($uuid);

                header('Location: /president-upload-final-document-form');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro: " . $e->getMessage();
                header('Location: /president-upload-final-document-form');
                exit();
            }
        }
    }

    public function invalidateDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $finalDocumentId = (int) $_POST['final_document_id'];
                $reason = $_POST['rejection_reason'];
                $email = $_POST['email'];

                error_log("Rejeitando documento com ID: $finalDocumentId e motivo: $reason");
                $smtpConfig = require __DIR__ . '/../../config/email.php';
                $emailController = new EmailController($smtpConfig);
                $emailController->sendRejectedValidationEmail($email, $finalDocumentId, $reason);

                $finalDocumentModel = new FinalDocument();
                $finalDocumentModel->updateStatus($finalDocumentId, 'Invalidado');

                (new LogsController)->logAction('invalidate-document');
                $_SESSION['message'] = "Documento rejeitado com sucesso!";
                header('Location: /need-validation-documents');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao rejeitar o documento: " . $e->getMessage();
                header('Location: /need-validation-documents');
                exit();
            }
        }
    }

    public function listPresidents(): void{
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_president_email"])){
            try{
                (new President)->createPresidentialEmail(trim($_POST["new_president_email"]));

                $presidentEmails = (new President)->getPresidentialEmails();
                $_SESSION["message"] = "Email presidencial adicionado com sucesso!";
                require __DIR__ . '/../Views/adminDashboard/listPresidentEmails.php';
            }catch (Exception $e){
                $_SESSION["error"] = "Erro! $e";
                require __DIR__ . '/../Views/adminDashboard/listPresidentEmails.php';
            }


        }else {
            $presidentEmails = (new President)->getPresidentialEmails();
            require __DIR__ . '/../Views/adminDashboard/listPresidentEmails.php';
        }
    }


    public function deletePresidentEmail(): void{
        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            try {
                (new President)->deletePresidentEmail((int)$_POST["email_id"]);

                $_SESSION["message"] = "Email presidencial removido com sucesso!";
                $this->goToPreviousPage();
            }catch (Exception $e){
                $_SESSION["error"] = "Erro! $e";
                $this->goToPreviousPage();
            }
        }
    }
}

?>
