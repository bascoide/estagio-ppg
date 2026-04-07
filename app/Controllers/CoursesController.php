<?php
namespace Ppg\Controllers;
use Ppg\Models\Course;
use PDOException;
use Ppg\Controllers\LogsController;

class CoursesController
{

    public function index(): void
    {
        $courseName = $_GET['course_name'] ?? '';
        $isActive = $_GET['is_active'] ?? null;

        $courseModel = new Course();

        $courses = $courseModel->getFilteredCourses($courseName, $isActive);
        $courseTypes = $courseModel->getCourseTypes();

        require __DIR__ . '/../Views/adminDashboard/coursesManagement.php';
    }

    public function toggleStatus(): void
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /courses');
            exit;
        }

        $courseModel = new Course();
        $success = $courseModel->toggleCourseStatus($id);

        if ($success) {
            (new LogsController)->logAction('edit-course');
            $_SESSION['message'] = 'Status do curso atualizado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar status do curso';
        }

        header('Location: /courses');
        exit;
    }

    public function addCourse(): void
    {
        $courseTypeId = (int) ($_POST['course_type'] ?? 0);
        $courseName = $_POST['course_name'] ?? null;
        $isActive = $_POST['is_course_active'] === '1'; // se '1' fica ativo, caso contrário inativo

        if (!$courseTypeId || !$courseName) {
            header('Location: /courses');
            exit;
        }

        $courseModel = new Course();
        try {
            $success = $courseModel->addCourse($courseName, $courseTypeId, $isActive);

            if ($success) {
                $_SESSION['message'] = 'Curso adicionado com sucesso!';
                (new LogsController)->logAction('create-course');
            } else {
                $_SESSION['error'] = 'Erro ao adicionar curso.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' && strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
                $_SESSION['error'] = 'Já existe um curso com esse nome.';
            } else {
                $_SESSION['error'] = 'Erro ao adicionar curso: ' . $e->getMessage();
            }
        }

        header('Location: /courses');
        exit;
    }
    public function editCourseName(): void
    {
        $id = $_POST['id'] ?? null;
        $newName = $_POST['new_name'] ?? null;
        if (!$id) {
            header('Location: /courses');
            exit;
        }

        $courseModel = new Course();
        $success = $courseModel->editCourseName($id, $newName);

        if ($success) {
            (new LogsController)->logAction('edit-course');
            $_SESSION['message'] = 'Nome do curso atualizado com successo!';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar nome do curso';
        }


        header('Location: /courses');
        exit;
    }

    public function deleteCourse(): void
    {
        $id = (int) $_POST['course_id'] ?? null;

        if (!$id) {
            header('Location: /courses');
            exit;
        }

        $courseModel = new Course();
        try {
            $success = $courseModel->deleteCourse($id);

            if ($success) {
                $_SESSION['message'] = 'Curso eliminado com sucesso!';
                (new LogsController)->logAction('delete-course');
            } else {
                $_SESSION['error'] = 'Erro ao eliminar curso.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' && strpos($e->getMessage(), '1451') !== false) {
                $_SESSION['error'] = 'Erro ao eliminar curso: O curso está em uso por algum utilizador.';
            } else {
                $_SESSION['error'] = 'Erro ao eliminar curso (erro na base de dados): ' . $e->getMessage();
            }
        }

        header('Location: /courses');
        exit;
    }

}
