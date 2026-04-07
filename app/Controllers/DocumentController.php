<?php


declare(strict_types=1);

namespace Ppg\Controllers;
use Ppg\Models\SubmittedPlans;
use ZipArchive;
use PDOException;
use Exception;
use Ppg\Models\FinalDocument;
use Ppg\Models\Document;
use Ppg\Models\Field;
use Ppg\Controllers\LogsController;

class DocumentController
{
    /**
     * @param int|null $finalDocumentId
     * @throws Exception
     */
    public function printDocument(?int $finalDocumentId = null): never
    {

        $documentId = $finalDocumentId;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_document_id'])) {
            $documentId = (int) $_POST['final_document_id'];
        }

        if (!$documentId) {
            http_response_code(400);
            echo "ID do documento não fornecido";
            exit();
        }

        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($documentId);

        if (!$document) {
            http_response_code(404);
            echo "Documento não encontrado";
            exit();
        }

        $filePath = realpath(__DIR__ . '/../../public/uploads/generated_docs/' . $document['pdf_path']);
        if (!$filePath || !file_exists($filePath)) {
            http_response_code(404);
            echo "Documento não encontrado";
            exit();
        }

        ob_clean();
        header("Content-type: application/pdf");
        header("Content-Length: " . filesize($filePath));
        header("Content-Disposition: inline; filename=" . basename($filePath));
        readfile($filePath);
        exit();
    }

    public function printDocumentForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'])) {
            $documentId = (int) $_POST['document_id'];
        }
        error_log("Document ID: " . $documentId);

        if (!$documentId) {
            http_response_code(400);
            echo "ID do documento não fornecido";
            exit();
        }

        $DocumentModel = new Document();
        $document = $DocumentModel->getDocumentById($documentId);

        if (!$document) {
            http_response_code(404);
            echo "Documento não encontrado";
            exit();
        }

        $filePath = realpath(__DIR__ . '/../../public/uploads/schema/' . $document['docx_path']);
        if (!$filePath || !file_exists($filePath)) {
            http_response_code(404);
            echo "Caminho não encontrado";
            exit();
        }

        // Clonar o ficheiro DOCX
        $clonedFilePath = __DIR__ . '/../../public/uploads/schema/temp_document.docx';
        copy($filePath, $clonedFilePath);

        $formController = new FormController();
        $this->replaceToEmpty($clonedFilePath, $document['type']);
        $pdfPath = $formController->convertToPdf($clonedFilePath);

        $path = realpath(__DIR__ . '/../../public/uploads/schema/' . $pdfPath);

        ob_clean();
        header("Content-type: application/pdf");
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: inline; filename=" . basename($path));
        readfile($path);

        // Eliminar o ficheiro PDF
        unlink($path);
        exit();
    }

    private function replaceToEmpty(string $clonedFilePath, string $type): bool
    {
        $emptySpace = $type === "Plano" ? "           " :  "___________";
        $zip = new ZipArchive();
        if ($zip->open($clonedFilePath) !== TRUE) {
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
                    else if (strtolower($fieldType) === 'radio' ||
                             strtolower($fieldType) === 'checkbox') {
                        // Para checkboxes, substitui por um espaço em branco
                        $processedXml = str_replace($fullPlaceholder, '☐', $processedXml, $count);
                        if ($count > 0) {
                            $replaceCount += $count;
                        }
                        continue;
                    }
                    else {
                        // Para outros tipos de campos, substitui por um espaço em branco
                        $processedXml = str_replace($fullPlaceholder, $emptySpace, $processedXml, $count);
                        if ($count > 0) {
                            $replaceCount += $count;
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

    public function viewPlan(): void
    {
        if (isset($_POST['plan_id'])) {
            $planId = (int) $_POST['plan_id'];
            $submittedPlansModel = new SubmittedPlans();
            $submittedPlansModel->verifyPlan($planId);
        }

        if (isset($_POST['plan_path'])) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $_POST['plan_path'];
            if (!file_exists($filePath)) {
                die("File not found: " . $filePath);
            }

            // Depuração: Checar permissões
            if (!is_readable($filePath)) {
                die("File not readable: " . $filePath);
            }
            ob_clean();
            header("Content-type: application/pdf");
            header("Content-Length: " . filesize($filePath));
            header("Content-Disposition: inline; filename=" . basename($filePath));
            readfile($filePath);
            exit();
        }
    }

    public function viewAddition(): void
    {
        if (isset($_POST['addition_path'])) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $_POST['addition_path'];
            if (!file_exists($filePath)) {
                die("File not found: " . $filePath);
            }

            // Depuração: Checar permissões
            if (!is_readable($filePath)) {
                die("File not readable: " . $filePath);
            }
            ob_clean();
            header("Content-type: application/pdf");
            header("Content-Length: " . filesize($filePath));
            header("Content-Disposition: inline; filename=" . basename($filePath));
            readfile($filePath);
            exit();
        }
    }

    /**
     * @throws Exception
     */
    public function downloadDocument(): never
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $documentId = (int) $_POST['id'];
        }

        if (!$documentId) {
            http_response_code(400);
            echo "ID do documento não fornecido";
            exit();
        }

        $documentModel = new Document();
        $document = $documentModel->getDocumentById($documentId);

        if (!$document) {
            http_response_code(404);
            echo "Documento não encontrado";
            exit();
        }

        $filePath = realpath(__DIR__ . '/../../public/uploads/schema/' . $document['docx_path']);
        if (!$filePath || !file_exists($filePath)) {
            http_response_code(404);
            echo "Documento não encontrado";
            exit();
        }

        ob_clean();
        header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Length: " . filesize($filePath));
        header("Content-Disposition: attachment; filename=" . basename($filePath));
        readfile($filePath);
        exit();
    }

    public function uploadDocumentForm(): void
    {
        require __DIR__ . '/../Views/adminDashboard/uploadDocuments.php';
    }

    /**
     * @throws Exception
     */
    public function createNewDocumentAndFields(): void
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /upload-document-form');
            exit();
        }

        $selectedCourseTypes = $_POST['courseTypes'] ?? [];
        if (empty($selectedCourseTypes)) {
            $_SESSION['error'] = "Selecione pelo menos um tipo de curso!";
            header('Location: /upload-document-form');
            exit();
        }

        if (
            !isset($_FILES['documentFile']) ||
            !isset($_POST['documentName']) ||
            !isset($_POST['documentType'])
        ) {
            $_SESSION['error'] = "Todos os campos são obrigatórios!";
            header('Location: /upload-document-form');
            exit();
        }

        try {
            $uploadDir = __DIR__ . '/../../public/uploads/schema/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                throw new Exception("Falha ao criar diretório de upload");
            }

            $fileName = $_POST['documentName'] . '.docx';
            $fileTmpName = $_FILES['documentFile']['tmp_name'];
            $fileType = $_FILES['documentFile']['type'];
            $fileError = $_FILES['documentFile']['error'];
            $documentName = $_POST['documentName'];
            $documentType = $_POST['documentType'];

            if ($fileError !== UPLOAD_ERR_OK) {
                throw new Exception("Erro no upload do ficheiro!");
            }

            $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($fileType, $allowedMimeTypes, true)) {
                throw new Exception("Apenas ficheiros .docx permitidos!");
            }

            $targetPath = $uploadDir . basename($fileName);
            if (!move_uploaded_file($fileTmpName, $targetPath)) {
                throw new Exception("Erro ao mover o ficheiro.");
            }

            $documentModel = new Document;
            $documentId = $documentModel->createDocument($fileName, $documentName, $documentType, $selectedCourseTypes);

            if (!$documentId) {
                throw new Exception("Falha ao criar documento na base de dados.");
            }

            $this->extractAndProcessDocument($targetPath, $documentId);

            (new LogsController)->logAction('upload-document');
            $_SESSION['message'] = "Documento carregado com sucesso!";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /upload-document-form');
        exit();
    }

    /**
     * @param string $filePath
     * @param int $documentId
     * @throws Exception
     */
    private function extractAndProcessDocument(string $filePath, int $documentId): void
    {
        error_log("DOCUMENT ID" . $documentId);
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Falha ao abrir ficheiro DOCX");
        }

        // Extrair texto do documento principal
        $text = '';
        $mainDoc = $zip->locateName('word/document.xml');
        if ($mainDoc !== false) {
            $xml = $zip->getFromIndex($mainDoc);
            if ($xml !== false) {
                $text .= strip_tags($xml);
            }
        }

        // Extrair texto dos cabeçalhos
        for ($i = 1; $i <= 3; $i++) {
            $headerIndex = $zip->locateName("word/header{$i}.xml");
            if ($headerIndex !== false) {
                $xml = $zip->getFromIndex($headerIndex);
                if ($xml !== false) {
                    $text .= strip_tags($xml);
                }
            }
        }

        // Extrair texto dos rodapés
        for ($i = 1; $i <= 3; $i++) {
            $footerIndex = $zip->locateName("word/footer{$i}.xml");
            if ($footerIndex !== false) {
                $xml = $zip->getFromIndex($footerIndex);
                if ($xml !== false) {
                    $text .= strip_tags($xml);
                }
            }
        }

        $zip->close();

        // Limpar o texto extraído
        $text = preg_replace('/\s+/', ' ', $text) ?? '';
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Extrair campos com padrões {name|dataType}
        $pattern = '/\{\s*([^|]+?)\s*\|\s*([^}]+?)\s*\}/';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Ficar o nome dos campos já utilizados
        $usedFieldNames = [];

        foreach ($matches as $match) {
            if (!isset($match[1], $match[2])) {
                continue;
            }

            $name = trim($match[1]);
            $dataType = trim($match[2]);

            if (in_array($name, $usedFieldNames, true)) {
                error_log("Campo duplicado ignorado: $name");
                continue;
            }

            $fieldModel = new Field();
            $result = $fieldModel->createField($documentId, $name, $dataType);

            if (!$result) {
                throw new Exception("Falha ao criar campo: $name");
            }

            // Adicionar nome dos campos para usar array de nomes
            $usedFieldNames[] = $name;
        }
    }

    /**
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    public function extractTextFromDocx(string $filePath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Falha ao abrir ficheiro DOCX");
        }

        $text = '';
        $index = $zip->locateName('word/document.xml');

        if ($index !== false) {
            $xml = $zip->getFromIndex($index);
            if ($xml !== false) {
                $text = strip_tags($xml);
                $text = preg_replace('/\s+/', ' ', $text) ?? '';
                $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
        }

        $zip->close();
        return $text;
    }

    public function deactivateDocument(): void
    {
        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            $documentId = (int) $_POST["id"];
            $documentName = $_POST["name"];

            try {
                $documentModel = new Document();
                $documentModel->deactivateDocument($documentId);

                (new LogsController)->logAction('deactivation-document');
                $_SESSION['message'] = "Documento " . $documentName . " apagado com sucesso!";
                header('Location: show-documents');
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao apagar o documento!";
                header('Location: show-documents');
            }
        }
    }

    public function activateDocument(): void
    {
        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            $documentId = (int) $_POST["id"];
            $documentName = $_POST["name"];

            try {
                $documentModel = new Document();
                $documentModel->activateDocument($documentId);

                (new LogsController)->logAction('restore-document');
                $_SESSION['message'] = "Documento " . $documentName . " restaurado com sucesso!";
                header('Location: show-documents');
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao restaurar o documento!";
                header('Location: show-documents');
            }
        }
    }
}
?>
