<?php

declare(strict_types=1);

namespace Ppg\Controllers;
use Exception;
use ZipArchive;
use PDO;
use Ppg\Models\Document;
use Ppg\Models\FieldValue;
use Ppg\Models\FinalDocument;
use Ppg\Models\Field;
use Ppg\Models\Course;
use Ppg\Models\User;
use Ppg\Models\Professor;
use Ppg\Controllers\EmailController;
use Ppg\Models\SubmittedPlans;

class FormController
{
    /**
     * @throws Exception
     */
    public function index(): void
    {
        require __DIR__ . '/../Views/guiaForm.php';
    }

    public function form(): void
    {
        $documentModel = new Document();

        $courseModel = new Course();
        [$userCourse] = $courseModel->getCourseByUserId((int) $_SESSION['user_id']);
        $typeCourseId = $userCourse['type_course_id'];

        $documents = $documentModel->getDocumentsByTypeCourseId($typeCourseId);
        require __DIR__ . '/../Views/form.php';
    }

    public function generateform(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $documentModel = new Document();

            $courseModel = new Course();
            $professorModel = new Professor();

            [$userCourse] = $courseModel->getCourseByUserId((int) $_SESSION['user_id']);
            $typeCourseId = $userCourse['type_course_id'];
            $userCourseName = $userCourse['name'];
            $courseId = $userCourse['id'];
            $availableProfessors = $professorModel->getProfessorByCourseId($courseId);

            error_log(print_r($availableProfessors, true));

            $documents = $documentModel->getDocumentsByTypeCourseId($typeCourseId);
            $documentId = isset($_GET['document']) ? (int) $_GET['document'] : null;

            if ($documentId === null) {
                header("Location: form");
                exit();
            }

            if ($documentId > 0) {
                $fieldModel = new Field();
                $fields = $fieldModel->getFieldsByDocumentId($documentId);
                require __DIR__ . '/../Views/form.php';
            } else {
                header("Location: form");
                exit();
            }
        }
    }


    /**
     * @throws Exception
     */
    public function submitForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Método inválido";
            header("Location: form");
            exit();
        }

        $documentId = (int) ($_POST['document_id'] ?? 0);
        if ($documentId <= 0) {
            $_SESSION['error'] = "ID do documento inválido";
            header("Location: form");
            exit();
        }

        try {
            $user = (int) $_SESSION['user_id'];
            $submittedData = [
                'field_ids' => $_POST['field_ids'] ?? [],
                'field_names' => $_POST['field_names'] ?? [],
                'field_values' => $_POST['field_values'] ?? []
            ];

            $planId = $this->processFileUpload();

            $finalDocumentId = $this->createFinalDocument($documentId, $user, $submittedData, $planId);
            if (!$finalDocumentId) {
                throw new Exception("Falha ao criar documento final");
            }

            $fieldValueModel = new FieldValue();
            if (!$fieldValueModel->createVariusFieldValues($documentId, $user, $submittedData, $finalDocumentId)) {
                throw new Exception("Falha ao salvar a resposta do formulário");
            }

            $this->sendSuccessNotification($user, $finalDocumentId, $planId !== null);
            header("Location: form");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: form");
            exit();
        }
    }

    private function processFileUpload(): ?int
    {
        if (!isset($_FILES['planFile']) || $_FILES['planFile']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/submittedPlans/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new Exception("Falha ao criar diretório de upload");
        }

        $fileType = $_FILES['planFile']['type'];
        if ($fileType !== 'application/pdf') {
            throw new Exception("Apenas ficheiros .pdf permitidos!");
        }

        $extension = pathinfo($_FILES['planFile']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('doc_', true) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['planFile']['tmp_name'], $targetPath)) {
            throw new Exception("Erro ao mover o ficheiro.");
        }

        $filePath = '/uploads/submittedPlans/' . $fileName;
        return (new SubmittedPlans())->createNewPlan($filePath);
    }

    private function sendSuccessNotification(int $userId, int $documentId, bool $hasPlanFile): void
    {
        if ($hasPlanFile) {
            // Submissão de Protocolo (com ficheiro) - sem email
            $_SESSION['message'] = "Protocolo submetido com sucesso!";
        } else {
            // Submissão de Palno (sem ficheiro) - enviar email
            $_SESSION['message'] = "Plano submetido com sucesso! Irá receber um email, proceda através do mesmo.";

            $userModel = new User();
            $userEmail = $userModel->getUserById($userId)['email'];

            $smtpConfig = require __DIR__ . '/../../config/email.php';
            (new EmailController($smtpConfig))->sendPlanEmail($userEmail, $documentId);
        }
    }

    /**
     * @param int $documentId
     * @param int $userId
     * @param array<string, array<int, string>> $submittedData
     * @return int
     * @throws Exception
     */
    public function createFinalDocument(int $documentId, int $userId, array $submittedData, ?int $planId = null): int
    {
        $docxPath = $this->generateFinalDocx($documentId, $submittedData);
        $pdfPath = $this->convertToPdf($docxPath);

        if (isset($_POST['status'])) {
            $status = $_POST['status'];
        } else {
            $status = 'Pendente';
        }

        $finalDocumentModel = new FinalDocument();
        $result = $finalDocumentModel->createFinalDocument($userId, $pdfPath, $documentId, $status, $planId);

        if (!$result) {
            throw new Exception("Falha ao criar final document record");
        }

        return (int) $result;
    }

    /**
     * @param int $documentId
     * @param array<string, array<int, string>> $submittedValues
     * @return string
     * @throws Exception
     */
    private function generateFinalDocx(int $documentId, array $submittedValues): string
    {
        $documentModel = new Document();
        $document = $documentModel->getDocumentById($documentId);

        if (!$document) {
            throw new Exception("Documento não encontrado");
        }

        $basePath = realpath(__DIR__ . '/../../public/uploads/');
        if (!$basePath) {
            throw new Exception("Caminho base não encontrado");
        }

        $templatePath = $basePath . '/schema/' . $document['docx_path'];
        $outputDir = $basePath . '/generated_docs/';

        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new Exception("Falha ao criar diretório output");
        }

        $outputDocxPath = $outputDir . 'document_' . $documentId . '_' . time() . '.docx';
        if (!copy($templatePath, $outputDocxPath)) {
            throw new Exception("Falha ao copiar template");
        }

        $this->replacePlaceholders($templatePath, $outputDocxPath, $submittedValues);

        return $outputDocxPath;
    }

    /**
     * @param string $docxPath
     * @return string
     * @throws Exception
     */
    public function convertToPdf(string $docxPath): string
    {
        $outputDir = dirname($docxPath);
        $pdfPath = preg_replace('/\.docx$/', '.pdf', $docxPath);

        $command = sprintf(
            'soffice --headless --convert-to pdf --outdir %s %s',
            escapeshellarg($outputDir),
            escapeshellarg($docxPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($pdfPath)) {
            error_log("Conversão para PDF falhou. Command output: " . implode("\n", $output));
            throw new Exception("Falha ao converter documento para PDF");
        }

        error_log("Sucesso ao criar PDF em: " . $pdfPath);

        // Deletar o ficheiro DOCX original
        if (file_exists($docxPath)) {
            if (!unlink($docxPath)) {
                error_log("Warning: Não conseguiu deletar o ficheiro DOCX original: " . $docxPath);
            }
        }

        return basename($pdfPath);
    }

    /**
     * @param string $templateDocument
     * @param string $outputPath
     * @param array<string, array<int, string>> $submittedValues
     * @return bool
     * @throws Exception
     */
    private function replacePlaceholders(string $templateDocument, string $outputPath, array $submittedValues): bool
    {
        error_log(print_r($submittedValues, true));
        if (
            !isset($submittedValues['field_names']) ||
            !is_array($submittedValues['field_names']) ||
            !isset($submittedValues['field_values']) ||
            count($submittedValues['field_names']) !== count($submittedValues['field_values'])
        ) {
            throw new Exception("field_data está mal formada. Esperava arrays paralelas de field_names e field_values.");
        }

        if (!copy($templateDocument, $outputPath)) {
            throw new Exception("Falha ao criar cópia de trabalho do documento base.");
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== TRUE) {
            throw new Exception("Falha ao abrir ficheiro DOCX para modificação.");
        }

        $replaceCount = 0;

        // Lista de ficheiros a processar (document.xml, headers e footers)
        // word/document.xml é o ficheiro principal
        $filesToProcess = ['word/document.xml'];

        for ($i = 1; $i <= 3; $i++) {
            $filesToProcess[] = "word/header{$i}.xml";
            $filesToProcess[] = "word/footer{$i}.xml";
        }
        error_log(print_r($filesToProcess, true));

        // Process each file
        foreach ($filesToProcess as $fileName) {
            $xml = $zip->getFromName($fileName);
            if ($xml === false) {
                error_log("Warning: Não conseguiu abrir {$fileName} no DOCX.");
                continue; //Pula ficheiros que não existem
            }

            $pattern = '/\{(?:[^<{}]+|<[^>]+>)*?\|\s*(?:[^<{}]+|<[^>]+>)*?\}/';
            preg_match_all($pattern, $xml, $matches, PREG_SET_ORDER);

            $processedXml = $xml;
            foreach ($matches as $match) {
                $fullPlaceholder = $match[0];
                $cleanPlaceholder = preg_replace('/<[^>]+>/', '', $fullPlaceholder);

                if (preg_match('/\{\s*([^|}]+?)\s*\|\s*([^}]+?)\s*\}/', $cleanPlaceholder, $parts)) {
                    $fieldName = trim($parts[1]);
                    $fieldType = trim($parts[2]);

                    if (strtolower($fieldType) === 'title' ||
                        strtolower($fieldType) === 'group' ||
                        $fieldType === 'NA start' ||
                        $fieldType === 'NA end' ) {

                        $processedXml = str_replace($fullPlaceholder, '', $processedXml, $count);
                        if ($count > 0) {
                            $replaceCount += $count;
                        }
                        continue;
                    }

                    foreach ($submittedValues['field_names'] as $index => $submittedName) {
                        $cleanSubmittedName = trim($submittedName);

                        if ($cleanSubmittedName === $fieldName && isset($submittedValues['field_values'][$index])) {
                            $value = $submittedValues['field_values'][$index];

                            if ($value === 'true') {
                                $value = '☒';
                            } else if ($value === 'false') {
                                $value = '☐';
                            }

                            $xmlSafeValue = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                            $processedXml = str_replace($fullPlaceholder, $xmlSafeValue, $processedXml, $count);
                            if ($count > 0) {
                                $replaceCount += $count;
                            }
                            break;
                        }
                    }
                }
            }

            // Atualiza o ficheiro no arquivo ZIP se mudanças forem feitas
            if ($processedXml !== $xml) {
                if ($zip->deleteName($fileName)) {
                    if (!$zip->addFromString($fileName, $processedXml)) {
                        $zip->close();
                        throw new Exception("Falha ao adicionar {$fileName} modificado");
                    }
                } else {
                    $zip->close();
                    throw new Exception("Falha ao remover {$fileName} original");
                }
            }
        }

        if (!$zip->close()) {
            throw new Exception("Falha ao salvar mudanças para o ficheiro DOCX");
        }

        return $replaceCount > 0;
    }
}
