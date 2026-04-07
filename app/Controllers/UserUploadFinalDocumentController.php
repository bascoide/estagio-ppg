<?php
namespace Ppg\Controllers;

use Ppg\Models\FinalDocument;
use Ppg\Models\User;

use Exception;

class UserUploadFinalDocumentController
{
    public function index()
    {
        require __DIR__ . '/../Views/userUploadFinalDocument.php';
    }

    public function uploadFinalDocument(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['final_document_id'])) {
            if (!isset($_FILES['document'])) {
                $_SESSION['error'] = "Nenhum arquivo foi enviado.";
                header('Location: /user-upload-final-document-form?final_document_id=' . $_GET['final_document_id']);
                exit;
            }

            $file = $_FILES['document'];

            $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($fileType !== 'pdf') {
                $_SESSION['error'] = "Apenas arquivos PDF são permitidos.";
                header('Location: /user-upload-final-document-form?final_document_id=' . $_GET['final_document_id']);
                exit;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/generated_docs/';

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $oldFinalDocumentId = (int) $_GET['final_document_id'];

            $finalDocumentModel = new FinalDocument();
            $oldFinalDocument = $finalDocumentModel->getFinalDocumentById($oldFinalDocumentId);

            $this->authenticateUser($oldFinalDocument['user_id']);

            // Gerar filenames únicos para prevenir substituições
            $filename = uniqid() . '_' . $file['name'];
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $finalDocumentModel->createFinalDocument(
                    $oldFinalDocument['user_id'],
                    $filename,
                    (int) $oldFinalDocument['document_id'],
                    'Por validar',
                    (int) $oldFinalDocument['plan_id']
                );


                $_SESSION['message'] = "Upload realizado com sucesso!";
                header('Location: /user-upload-final-document-form');
                exit;
            } else {
                error_log('Falha no upload: ' . error_get_last()['message']);
                $_SESSION['error'] = "Falha ao fazer upload do documento.";
                header('Location: /user-upload-final-document-form?final_document_id=' . $oldFinalDocumentId);
            }
        } else {
            echo "Chamamento do método inválido.";
        }
    }

    private function authenticateUser(int $oldUserId): void
    {
        $userModel = new User();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Email e senha são obrigatórios!";
            require __DIR__ . '/../Views/login.php';
            return;
        }

        try {
            $user = $userModel->findUser($email);

            if ($user !== null && password_verify($password, $user['password'])) {
                if ($user['id'] !== $oldUserId) {
                    $_SESSION['error'] = 'Não tem permissão para fazer upload deste documento!';
                    header('Location: user-upload-final-document-form?final_document_id=' . $_GET['final_document_id']);
                    exit;
                } else {
                    return;
                }
            } else {
                $_SESSION['error'] = "Email ou senha inválidos!";
                header('Location: user-upload-final-document-form?final_document_id=' . $_GET['final_document_id']);
                exit;
            }

        } catch (Exception $ex) {
            error_log('Erro ao buscar utilizador: ' . $ex->getMessage());
        }
    }
}

?>