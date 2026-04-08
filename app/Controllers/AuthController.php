<?php

declare(strict_types=1);

namespace Ppg\Controllers;
use Ppg\Models\User;
use Ppg\Models\Course;
use Ppg\Controllers\EmailController;

use PDO;
use Exception;

class AuthController
{
    /**
     * @throws Exception
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                if (!isset($user)){
                    $_SESSION['error'] = "Email ou senha inválidos!";
                    require __DIR__ . '/../Views/login.php';
                    exit();
                }

                if ($user["verified"] === 0) {
                    $_SESSION['error'] = "Verifique a sua conta primeiro! Use o link enviado no seu email.";
                    require __DIR__ . '/../Views/login.php';
                    exit();
                }

                if ($user !== null && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['admin'] = (bool) $user['admin'];

                    if ($_SESSION['admin'] === true) {
                        header('Location: set-name');
                        exit();
                    }

                    header('Location: guia-form');
                    exit();
                } else {
                    $_SESSION['error'] = "Email ou senha inválidos!";
                    require __DIR__ . '/../Views/login.php';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao fazer login: " . $e->getMessage();
                require __DIR__ . '/../Views/login.php';
            }
        } else {
            require __DIR__ . '/../Views/login.php';
        }
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();

            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($name) || empty($email) || empty($password)) {
                $_SESSION['error'] = "Email e senha são obrigatórios!";
                header('Location: /register');
                return;
            }

            if (!preg_match('/@iscap\.ipp\.pt$/', $email)) {
                $_SESSION['error'] = "Apenas emails com domínio @iscap.ipp.pt são permitidos!";
                header('Location: /register');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            try {
                $existingUser = $userModel->findUser($email);

                if ($existingUser !== null && $existingUser["verified"] === 1) {
                    $_SESSION['error'] = "Email já está registado!";
                    header('Location: /register');
                    return;
                }

                if ($existingUser["verified"] === 0) {
                    $userModel->deleteUnverifiedUser($email);
                }

                $verificationCode = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(24))), 0, 32);
                error_log("email: " . $email . "");
                $result = $userModel->createUser($name, $email, $hashedPassword, false, (int) $_POST["Course"], $verificationCode, false);
                if (!$result) {
                    throw new Exception("Falha ao criar utilizador");
                }

                $smtpConfig = require __DIR__ . '/../../config/email.php';

                $emailController = new EmailController($smtpConfig);
                $emailController->sendComfirmationCode($email, $verificationCode);

                $_SESSION['message'] = "Utilizador criado com sucesso! Verifique o seu email para acabar a verificação.";
                header('Location: /login');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao criar utilizador: " . $e->getMessage();
                header('Location: /register');
            }

        } else {

            $courseModel = new Course();
            $courses = $courseModel->showCourses();

            $courseTypeModel = new Course();
            $coursesTypes = $courseTypeModel->getCourseTypes();

            require __DIR__ . '/../Views/register.php';
        }

    }

    public function verifyUser(): void
    {
        if (isset($_GET['email']) && isset($_GET['verification_code'])) {
            $email = $_GET['email'];
            $verificationCode = $_GET['verification_code'];
            $userModel = new User();

            $success = $userModel->verifyUser($email, $verificationCode);

            if ($success) {
                $_SESSION['message'] = "Conta verificada com sucesso!";
                require __DIR__ . '/../Views/verifyUser.php';
            } else {
                $_SESSION['error'] = "Erro ao verificar conta :(";
                require __DIR__ . '/../Views/verifyUser.php';
            }
        } else {
            $_SESSION['error'] = "Link inválido";
            require __DIR__ . '/../Views/verifyUser.php';
        }
    }

    /**
     * @return never
     */
    public function logout(): never
    {
        session_destroy();
        header('Location: login');
        exit();
    }


    public function setAdminName(): void
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["admin_name"])) {
            $_SESSION["admin_name"] = $_POST["admin_name"];
            header("Location: view-pending-documents");
        } else {
            require __DIR__ . '/../Views/adminDashboard/setName.php';
        }
    }
}
?>