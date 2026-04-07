<?php
namespace Ppg\Controllers;
use Ppg\Models\Professor;
use Ppg\Models\Course;
use Ppg\Models\FinalDocument;
use Exception;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DateTime;

class ProfessorController
{
    public function index()
    {
        $professorModel = new Professor();
        $courseModel = new Course();
        $courses = $courseModel->showCourses();

        $searchName = null;
        $selectedCourse = null;
        $professors = [];

        // Apenas processa pesquisa se os parâmetros estão presentes
        if (
            $_SERVER['REQUEST_METHOD'] === 'GET' &&
                (!empty($_GET['search']) || isset($_GET['course_id']))
        ) {

            $searchName = $_GET['search'] ?? null;
            $selectedCourse = isset($_GET['course_id']) && $_GET['course_id'] !== ''
            ? (int) $_GET['course_id']
            : null;

            $professors = $professorModel->getProfessorByFilter($searchName, $selectedCourse);
        } else {
            $professors = $professorModel->getAllProfessors();
        }

        require __DIR__ . '/../Views/adminDashboard/professorSearch.php';
    }

    public function professorDocuments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['professor_id'])) {
            header('Location: /adminDashboard/professors');
            exit;
        }

        try {
            $professorId = (int) $_GET['professor_id'];
            $professorModel = new Professor();

            $professor = $professorModel->getProfessorById($professorId);

            $courses = $professorModel->getCoursesByProfessorId($professorId);

            $documentsFilledAt = $professorModel->getDocumentCreatedAtByProfessorId($professorId);
            error_log("Documents Filled At: " . print_r($documentsFilledAt, true));

            $schoolYears = [];

            foreach ($documentsFilledAt as $filledAt){
                $documentDate = new DateTime($filledAt['created_at']);

                if ($documentDate->format('m') >= 8) {
                    $schoolYearStart = $documentDate->format('Y');
                    $schoolYearEnd = $documentDate->format('Y') + 1;
                } else {
                    $schoolYearStart = $documentDate->format('Y') -1;
                    $schoolYearEnd = $documentDate->format('Y');
                }
                $schoolYears[] = "$schoolYearStart/$schoolYearEnd";
            }

            $schoolYears = array_unique($schoolYears);

            require __DIR__ . '/../Views/adminDashboard/createProfessorDocument.php';

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /adminDashboard/professors');
            exit;
        }
    }

    private function defineSchoolYear(?string $documentFilledAt): ?string
    {
        if (!$documentFilledAt) {
            return null;
        }

        try {
            $date = new \DateTime($documentFilledAt);
            $year = (int) $date->format('Y');
            $month = (int) $date->format('m');

            // Ano Letivo comeca em setembro e termina em agosto
            return ($month >= 9)
                ? sprintf("%d/%d", $year, $year + 1)
                : sprintf("%d/%d", $year - 1, $year);

        } catch (Exception $e) {
            error_log("Error calculating school year: " . $e->getMessage());
            return null;
        }
    }

    private function cleanCourseName($courseName)
    {
        $prefixes = [
            'Licenciatura em ',
            'Mestrado em ',
            'Pós-Graduação em ',
            'Pós-Graduação ',
            'CTeSP de ',
            'CTeSP em '
        ];
        return str_ireplace($prefixes, '', $courseName);
    }

    public function createReport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /professor-search');
            exit;
        }

        if (!isset($_POST['professor_id']) || !isset($_POST['school_year'])) {
            header('Location: /professor-search');
            exit;
        }

        $professorId = (int) $_POST['professor_id'];
        $schoolYear = $_POST['school_year'];
        $courseId = (int) $_POST['course_id'] ?? null;

        $professorModel = new Professor();
        $courseModel = new Course();

        $interns = $professorModel->getInternsByProfessorCourseAndYear($professorId, $schoolYear, $courseId);
        error_log("Interns: " . print_r($interns, true));
        $professorName = $professorModel->getProfessorById($professorId)['name'];
        $courseName = $courseModel->getCourseNameById($courseId);

        error_log("Interns: " . print_r($interns, true));
        require __DIR__ . '/../../vendor/autoload.php';
        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        $headert = $section->addHeader();

        $footer = $section->addFooter();

        $tableH = $headert->addTable();
        $tableH->addRow();
        $tableH->addCell(10000)->addImage(
            'images/logo_relatorios.png',
            array(
                'width' => 130,
                'height' => 30,
                'align' => 'right'
            )
        );

        $footer->addPreserveText(
            '{PAGE} de {NUMPAGES}',
            null,
            ['alignment' => 'right']
        );

        $section->addText(
            'ANEXO',
            ['name' => 'Times New Roman', 'size' => 18, 'bold' => true],
            ['align' => 'center']
        );

        $section->addText(
            'Discriminação dos alunos orientados por ano letivo - ' . $professorName,
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
            ['align' => 'center']
        );

        $section->addText(
            '' . $courseName,
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
            ['align' => 'center']
        );

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'align' => 'center'
        ]);

        // Adiciona o cabeçalho da tabela
        $table->addRow();
        $table->addCell(2000)->addText('Ano letivo', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText('Curso', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText('Aluno', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText('Tema', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true], ['align' => 'center']);

        $internCount = 0;
        // Adiciona os dados dos alunos
        foreach ($interns as $intern) {
            $internCount++;
            $cleanedCourseName = $this->cleanCourseName($courseName);
            $table->addRow();
            $table->addCell()->addText($schoolYear, ['name' => 'Times New Roman', 'size' => 11, 'bold' => false]);
            $table->addCell()->addText($cleanedCourseName, ['name' => 'Times New Roman', 'size' => 11, 'bold' => false]);
            $table->addCell()->addText($intern['intern_name'], ['name' => 'Times New Roman', 'size' => 11, 'bold' => false]);
            $table->addCell()->addText('Relatório de estágio', ['name' => 'Times New Roman', 'size' => 11, 'bold' => false]);
        }

        // Salvar o documento
        $filename = 'Relatório_estágio.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);

        $this->createFinalReport($professorName, $courseName, $schoolYear, $internCount);

        $_SESSION['message'] = "Relatório criado com sucesso!";

        $filename1 = 'Relatório_estágio.docx';
        $filename2 = 'Declaração_orientação_estágios.docx';

        $this->downloadMultipleFiles([$filename1, $filename2], $professorName.'.zip');
    }

    private function createFinalReport(
        string $professorName,
        string $courseName,
        string $schoolYear,
        int $internCount
    ): void {
        require __DIR__ . '/../../vendor/autoload.php';

        $templatePath = 'templates/template_final_report.docx';
        $template = new TemplateProcessor($templatePath);

        $template->setValue('professor_name', $professorName);
        $template->setValue('course_name', $courseName);
        $template->setValue('school_year', $schoolYear);
        $template->setValue('intern_count', $internCount);

        setlocale(LC_TIME, 'pt_PT.UTF-8', 'pt_PT.utf8', 'portuguese');
        $currentDate = strftime('%d de %B de %Y');
        $template->setValue('current_date', $currentDate);

        $outputPath = 'Declaração_orientação_estágios.docx';
        $template->saveAs($outputPath);
    }
    private function downloadMultipleFiles(array $filePaths, string $zipName = 'reports.zip')
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        foreach ($filePaths as $filePath) {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: $filePath");
            }
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new \ZipArchive();

        if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            unlink($tempZip);
            throw new Exception('Cannot create zip file');
        }

        foreach ($filePaths as $filePath) {
            $zip->addFile($filePath, basename($filePath));
        }

        if (!$zip->close()) {
            unlink($tempZip);
            throw new Exception('Cannot finalize zip file');
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($tempZip));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tempZip);

        unlink($tempZip);
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        exit;
    }


    // Cria um excel com todos os alunos com um protocolo válido ou aceite
    public function createStatusExcel(): void {
        // limpar buffers
        if (ob_get_length()) ob_end_clean();

        $teacherName = $_POST['teacher_name'];
        $finalDocumentModel = new FinalDocument();
        
        $documents = $finalDocumentModel->getDocumentStatusTeacher($teacherName);
        error_log("Documents: " . print_r($documents, true));

        if (empty($documents)) {
            $_SESSION['error'] = 'Este professor não tem nenhum aluno com documentos aceites ou válidos!';
            header("Location: /professor-search");
            exit();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Status');
        $sheet->setCellValue('B1', 'Data de Entrega');
        $sheet->setCellValue('C1', 'Nome do Aluno');
        $sheet->setCellValue('D1', 'Email');

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'D3D3D3']
            ]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($documents as $document) {
            $sheet->setCellValue('A' . $row, $document['status']);
            $sheet->setCellValue('B' . $row, $document['delivered_at']);
            $sheet->setCellValue('C' . $row, $document['student_name']);
            $sheet->setCellValue('D' . $row, $document['email']);

            $rowStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => $document['status'] == 'Aceite' ? 'ea9927' /* amarelo se for verdadeiro */ : '6efc16' /* verde se for falso */]
                ]
            ];
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($rowStyle);

            $row++;
        }

        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // salvar localmente temporariamente
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        // Download no lado do cliente
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="protocolos_alunos '. $teacherName . '.xlsx"');
        header('Cache-Control: max-age=0');
        readfile($tempFile);
        unlink($tempFile);
        exit;
    }
}
?>
