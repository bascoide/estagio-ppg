<?php
namespace Ppg\Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Ppg\Models\FinalDocument;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Ppg\Models\President;

class EmailController
{
    private $config;

    public function __construct(array $smtpConfig)
    {
        $requiredKeys = [
            'host',
            'username',
            'password',
            'encryption',
            'port',
            'timeout',
            'from_email',
            'from_name'
        ];

        foreach ($requiredKeys as $key) {
            if (!isset($smtpConfig[$key])) {
                throw new \InvalidArgumentException("Missing required SMTP config key: {$key}");
            }
        }

        $this->config = $smtpConfig;
    }

    private function send(
        string $to,
        string $subject,
        string $message,
        bool $isHtml = true,
        array $attachments = [],
        string $replyTo = null
    ): bool {
        $mail = new PHPMailer(true);

        try {
            // Configuração SMTP
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'];
            $mail->Port = $this->config['port'];
            $mail->Timeout = $this->config['timeout'];

            // Emissor e recetor
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);

            // Opcional reply-to
            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            // Anexos
            foreach ($attachments as $filePath) {
                $mail->addAttachment($filePath);
            }

            // Conteúdo do Email
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $message;

            if (!$isHtml) {
                $mail->AltBody = strip_tags($message);
            }

            return $mail->send();

        } catch (Exception $e) {
            error_log('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendAcceptedEmail(string $userEmail, int $finalDocumentId): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $rootUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/';
        $fullUrl = $rootUrl . "user-upload-final-document-form?final_document_id=" . $finalDocumentId;

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Protocolo Aprovado</title>
            </head>
            <body>
                <h2>Protocolo Aprovado</h2>
                <p>Prezada(o),</p>
                <p>Informamos que o protocolo foi <strong>aprovado</strong> e processado com sucesso.</p>
                <p>Por favor, solicite as assinaturas necessárias para a finalização do mesmo.</p>
                <p>O documento encontra-se em anexo a este e-mail.</p>
                <p>Em caso de dúvidas, estamos à disposição para atendê-lo(a).</p>
                <br>
                <p>Submita o seu documento para assinatura através do seguinte link:</p>
                <p><a href='" . htmlspecialchars($fullUrl) . "'>Clique aqui para submeter o documento</a></p>
                <p>Se não solicitou este documento, por favor, desconsidere esta mensagem.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";

        return $this->send(
            $userEmail,
            'O Seu Protocolo foi Aprovado',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email']
        );
    }

    public function sendRejectedEmail(string $userEmail, int $finalDocumentId, string $rejectionReason = '', array $rejectedFields): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Protocolo Rejeitado</title>
            </head>
            <body>
                <h2>Protocolo Rejeitado</h2>
                <p>Prezada(o),</p>
                <p>Lamentamos informar que o seu protocolo foi <strong>rejeitado</strong>.</p>"
            . (!empty($rejectionReason) ? "<p><strong>Motivo da rejeição:</strong> <br>" . htmlspecialchars($rejectionReason) . "</p>" : "") . "
                <ul>" .
                (!empty($rejectedFields) ?
                    "<p><strong>Campos rejeitados:</strong></p>" .
                    implode('', array_map(function($fieldName, $fieldValue) {
                        return "<li>" . htmlspecialchars($fieldName) . ": " . htmlspecialchars($fieldValue) . "</li>";
                    }, array_keys($rejectedFields), $rejectedFields))
                : "") .
                "</ul>
                <br>
                <p>O documento está anexado para sua referência.</p>
                <p>Solicitamos que reveja o conteúdo assinalado e, se necessário, envie novamente para análise.</p>
                <p>Em caso de dúvidas, estamos à disposição.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";


        return $this->send(
            $userEmail,
            'O Seu Protocolo foi Rejeitado',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email']
        );
    }

    public function sendRejectedValidationEmail(string $userEmail, int $finalDocumentId, string $rejectionReason = ''): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Protocolo Invalidado</title>
            </head>
            <body>
                <h2>Protocolo Invalidado</h2>
                <p>Prezada(o),</p>
                <p>Lamentamos informar que o seu protocolo foi <strong>rejeitado</strong>.</p>"
            . (!empty($rejectionReason) ? "<p><strong>Motivo da rejeição:</strong> <br>" . htmlspecialchars($rejectionReason) . "</p>" : "") . "
                <br>
                <p>Por favor, consulte o e-mail enviado anteriormente.</p>
                <p>Solicitamos que reveja o conteúdo assinalado e envie novamente para análise.</p>
                <p>Em caso de dúvidas, estamos à disposição.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";


        return $this->send(
            $userEmail,
            'O Seu Protocolo foi Invalidado',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email']
        );
    }

    public function sendPresidentialValidatonEmail(string $presidentEmail, int $finalDocumentId, int $userId, string $adminName): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $uuid = (new President())->getPresidentAproveRequest($finalDocumentId)['uuid'];
        $rootUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/';
        $fullUrl = $rootUrl . "president-upload-final-document-form?uuid=" . $uuid;
        
        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Protocolo em Espera</title>
            </head>
            <body>
                <h2>Deve por favor assinar este protocolo</h2>
                <p>Bom dia,</p>
                <p>Em anexo encontra um protocolo, para por favor, assinar.</p>
                <p>Submeta-o assinado através do seguinte link:</p>
                <p><a href='" . htmlspecialchars($fullUrl) . "'>Clique aqui para submeter o documento</a></p>
                <p>Qualquer questão, contacte o GEE.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
                <p>" . htmlspecialchars($adminName) . "</p>
            </body>
            </html>
        ";

        return $this->send(
            $presidentEmail,
            'O Protocolo está à espera da sua assinatura',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email'] 
        );
    }

    public function sendAcceptedValidationEmail(string $userEmail, int $finalDocumentId): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Protocolo Validado</title>
            </head>
            <body>
                <h2>Protocolo Validado</h2>
                <p>Prezada(o),</p>
                <p>Informamos que o seu protocolo foi <strong>aprovado</strong> e finalizado com sucesso.</p>
                <p>O processo encontra-se concluído, não sendo necessária qualquer ação adicional da sua parte.</p>
                <p>Poderá encontrar o documento finalizado em anexo a este e-mail.</p>
                <p>Em caso de dúvidas, estamos ao dispor.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";

        return $this->send(
            $userEmail,
            'O Seu Protocolo foi Validado',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email']
        );
    }

    public function sendCancelledEmail(string $userEmail, int $finalDocumentId): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Protocolo Anulado</title>
            </head>
            <body>
                <h2>Protocolo Anulado</h2>
                <p>Prezada(o),</p>
                <p>Informamos que o seu protocolo foi <strong>anulado</strong>.</p>
                <p>Esta decisão não é automática e foi realizada a seu pedido.</p>
                <p>Poderá encontrar o documento anulado em anexo a este e-mail.</p>
                <p>Em caso de dúvidas, estamos ao dispor.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";

        return $this->send(
            $userEmail,
            'O Seu Protocolo foi anulado',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email']
        );
    }

    public function sendPlanEmail(string $userEmail, int $finalDocumentId): bool
    {
        $finalDocumentModel = new FinalDocument();
        $document = $finalDocumentModel->getFinalDocumentById($finalDocumentId);

        $pdfPath = __DIR__ . '/../../public/uploads/generated_docs/' . $document["pdf_path"];

        if (!$document || !file_exists($pdfPath)) {
            error_log("Documento não encontrado ou faltando caminho PDF por ID: $finalDocumentId");
            return false;
        }

        $rootUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/';
        $fullUrl = $rootUrl . "form?filled_plan_id=" . $finalDocumentId;

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Plano Pendente</title>
            </head>
            <body>
                <h2>Plano Pendente</h2>
                <p>Prezada(o),</p>
                <p>Informamos que o plano está <strong>pendente</strong>.</p>
                <p>Por favor, solicite as assinaturas necessárias para a finalização do mesmo.</p>
                <p>O documento encontra-se em anexo a este e-mail.</p>
                <p>Em caso de dúvidas, estamos à disposição para atendê-lo(a).</p>
                <br>
                <p>Submita o seu documento através do seguinte link:</p>
                <p><a href='" . htmlspecialchars($fullUrl) . "'>Clique aqui para prosseguir</a></p>
                <p>Se não solicitou este documento, por favor, desconsidere esta mensagem.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";

        return $this->send(
            $userEmail,
            'O Seu Plano está Pendente',
            $html,
            true,
            [$pdfPath],
            $this->config['from_email']
        );
    }


    public function sendComfirmationCode(string $userEmail, string $verificationCode): bool
    {
        $rootUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/';
        $fullUrl = $rootUrl . "user-verification?email=" . $userEmail . "&verification_code=" . $verificationCode;

        $html = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Verificação de Conta</title>
            </head>
            <body>
                <h2>Verifique a sua conta!</h2>
                <p>Prezada(o),</p>
                <p>Para concluir a verificação da sua conta, clique no link abaixo:</p>
                <p><a href='" . htmlspecialchars($fullUrl) . "'>Clique aqui para verificar</a></p>
                <p>Se não solicitou este cadastro, por favor, desconsidere esta mensagem.</p>
                <br>
                <p>Saudações académicas,</p>
                <p><strong>Equipa de Atendimento</strong><br>
                ISCAP</p>
            </body>
            </html>
        ";

        return $this->send(
            $userEmail,
            'Verificação da sua Conta',
            $html,
            true,
            [],
            $this->config['from_email']
        );

    }
}
