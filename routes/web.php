<?php
use Ppg\Controllers\CoursesController;
use Ppg\Controllers\ProfessorController;
use Ppg\Controllers\AuthController;
use Ppg\Controllers\DocumentController;
use Ppg\Controllers\FormController;
use Ppg\Controllers\AdminPanelController;
use Ppg\Controllers\UserUploadFinalDocumentController;
use Ppg\Controllers\DocumentValidationController;
use Ppg\Controllers\LogsController;

$authController = new AuthController();
$professorController = new ProfessorController();
$documentController = new DocumentController();
$formController = new FormController();
$adminPanelController = new AdminPanelController();
$userUploadFinalDocumentController = new UserUploadFinalDocumentController();
$documentValidationController = new DocumentValidationController();
$coursesController = new CoursesController();
$logsController = new LogsController();

// Pegar o request URI atual e remove query string
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remover diretório base se seu server não for root (ajustável se necessário)
$basePath = ''; // Muda para '/public' se seu server correr do diretório parente
$route = substr($requestUri, strlen($basePath));

// Normaliza a rota (remover barras iniciais/finais)
$route = trim($route, '/');
$route = $route ?: 'login'; // Default para login se a rota estiver vazia

// Recebe um request
switch ($route) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->login();
        } else {
            require __DIR__ . '/../app/Views/login.php';
        }
        break;

    case 'register':
        $authController->register();
        break;

    case 'user-verification':
        $authController->verifyUser();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'guia-form':
        if (isset($_SESSION['user_id'])) {
            $formController->index();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'form':
        if (isset($_SESSION['user_id'])) {
            $formController->form();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'get-form':
        if (isset($_SESSION['user_id'])) {
            $formController->generateform();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'submit-form':
        if (isset($_SESSION['user_id'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $formController->submitForm();
            }
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'print-pdf':
        if (isset($_SESSION['user_id'])) {
            $documentController->printDocument();
            break;
        } else {
            header('Location: /login');
            break;
        }

    case 'print-document':
        if (isset($_SESSION['user_id'])) {
            $documentController->printDocumentForm();
            break;
        } else {
            header('Location: /login');
            break;
        }

    case 'print-addition':
        if (isset($_SESSION['user_id'])) {
            $documentController->viewAddition();
            break;
        } else {
            header('Location: /login');
            break;
        }

    case 'download-docx':
        if (isset($_SESSION['user_id'])) {
            $documentController->downloadDocument();
            break;
        } else {
            header('Location: /login');
            break;
        }

    case 'user-upload-final-document-form':
        $userUploadFinalDocumentController->index();
        break;

    case 'user-upload-final-document':
        $userUploadFinalDocumentController->uploadFinalDocument();
        break;

    /////////////////
    ///ADMIN PANEL///
    /////////////////
    case 'view-pending-documents':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewPendingDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'need-validation-documents':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewNeedValidationDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'view-validation-documents':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewValidationDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'create-admin':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->createAdmin();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'show-users':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->showUsers();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'show-documents':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->showDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'user-documents':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewUserDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'addition-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewAdditionDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'upload-document-form':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentController->uploadDocumentForm();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'upload-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentController->createNewDocumentAndFields();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'president-upload-final-document-form':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentValidationController->presidentValidationPage();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'president-final-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentValidationController->presidentFinalDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'view-final-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewFinalDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'edit-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->editFinalDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'cancel-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->cancelFinalDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'validate-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentValidationController->validateDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'invalidate-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentValidationController->invalidateDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;


    case 'deactivate-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentController->deactivateDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'view-plan':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentController->viewPlan();
        } else {
            header('Location: /login');
            exit;
        }
        break;


    case 'activate-document':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentController->activateDocument();
        } else {
            header('Location: /login');
            exit;
        }
        break;


    case 'courses':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $coursesController->index();
        } else {
            header('Location: /login');
            exit;
        }
        break;


    case 'edit-course':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $coursesController->editCourseName();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'course/toggle-status':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $coursesController->toggleStatus();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'add-course':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $coursesController->addCourse();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'delete-course':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $coursesController->deleteCourse();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'professor-search':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $professorController->index();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'admin-documentation':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $adminPanelController->viewDocumentation();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'professor-documents':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $professorController->professorDocuments();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'create-report':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $professorController->createReport();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'create-status-excel':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $professorController->createStatusExcel();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'president-list':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentValidationController->listPresidents();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'delete-president-email':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $documentValidationController->deletePresidentEmail();
        } else {
            header('Location: /login');
            exit;
        }
        break;

    case 'admin-logs':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $logsController->index();
        } else {
            header('Location: /login');
            exit;
        }
        break;


    case 'set-name':
        if (isset($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $authController->setAdminName();
        } else {
            header('Location: /login');
            exit;
        }
        break;
    default:
        http_response_code(404);
        echo "Página não encontrada!";
        break;

}
