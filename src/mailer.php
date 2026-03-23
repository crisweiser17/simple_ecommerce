<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

function sendMailSMTP(string $to, string $subject, string $html, string $altText = ''): array {
    $enabled = getSetting('smtp_enabled', '0') === '1';
    if (!$enabled) {
        return ['success' => false, 'message' => 'SMTP disabled'];
    }

    $host = getSetting('smtp_host', 'smtp.resend.com');
    $port = (int) getSetting('smtp_port', '587');
    $username = getSetting('smtp_username', 'resend');
    $password = getSetting('smtp_password', '');
    $encryption = getSetting('smtp_encryption', 'tls'); // tls | ssl
    $fromEmail = getSetting('smtp_from_email', '');
    $fromName = getSetting('smtp_from_name', getSetting('store_name', 'R2 Research Labs'));
    $replyTo = getSetting('smtp_reply_to', '');

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            if ($port === 0) $port = 465;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            if ($port === 0) $port = 587;
        }
        $mail->Port = $port;

        if ($fromEmail) {
            $mail->setFrom($fromEmail, $fromName);
        }
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $altText ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo ?: $e->getMessage()];
    }
}

function renderEmailLayout(string $title, string $content): string {
    $brand = getSetting('store_name', 'R2 Research Labs');
    $headerBg = getSetting('theme_header_bg', '#0f1115');
    // Using white text for header since it's typically dark, but could also make it dynamic if there's a theme_header_text setting.
    $headerText = '#ffffff'; 

    return '
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>' . htmlspecialchars($brand) . ' — ' . htmlspecialchars($title) . '</title>
    </head>
    <body style="margin:0;padding:0;background-color:#f5f5f5;">
      <table role="presentation" style="width:100%;border-collapse:collapse;background-color:#f5f5f5;">
        <tr>
          <td align="center" style="padding:0;">
            <table role="presentation" style="width:100%;max-width:640px;border-collapse:collapse;margin:0 auto;">
              <tr>
                <td style="background:' . htmlspecialchars($headerBg) . ';color:' . htmlspecialchars($headerText) . ';padding:24px 28px;font-family:Helvetica,Arial,sans-serif;font-size:22px;font-weight:bold;letter-spacing:.3px;">
                  ' . htmlspecialchars($brand) . '
                </td>
              </tr>
              <tr>
                <td style="background:#ffffff;padding:28px;font-family:Helvetica,Arial,sans-serif;color:#111827;">
                  ' . $content . '
                </td>
              </tr>
              <tr>
                <td style="background:#ffffff;border-top:1px solid #e5e7eb;padding:18px 28px;font-family:Helvetica,Arial,sans-serif;color:#6b7280;font-size:12px;">
                  Para pesquisa científica somente. Não destinado ao consumo humano.
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>';
}

function renderLoginTokenEmail(string $code): string {
    $content = '
      <h1 style="margin:0 0 12px 0;font-size:20px;color:#111827;">Seu código de login</h1>
      <p style="margin:0 0 16px 0;line-height:1.55;">Use o código abaixo para concluir seu acesso. Ele expira em 15 minutos.</p>
      <div style="margin:18px 0;padding:18px 22px;border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;display:inline-block;">
        <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,\'Liberation Mono\',\'Courier New\',monospace;font-size:28px;letter-spacing:4px;color:#111827;">' . htmlspecialchars($code) . '</span>
      </div>
      <p style="margin:16px 0 0 0;line-height:1.55;font-size:14px;color:#4b5563;">Se você não solicitou este acesso, ignore este e-mail.</p>
    ';
    return renderEmailLayout('Login Code', $content);
}
