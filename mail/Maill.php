<?php

declare(strict_types=1);

/**
 * Módulo simple de envío de correos con plantillas (sin Composer).
 * Requiere los 3 archivos de PHPMailer en lib/phpmailer/.
 */
require_once __DIR__ . '/mail/lib/PHPMailer.php';
require_once __DIR__ . '/mail/lib/SMTP.php';
require_once __DIR__ . '/mail/lib/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class Mail
{
    /** Config SMTP (ajustar a tu cuenta Hostinger) */
    private const HOST       = 'smtp.hostinger.com';
    private const PORT       = 465;
    private const SECURE     = 'ssl';
    private const USERNAME   = 'contacto@sve.com.ar';
    private const PASSWORD   = 'W]17i|5HsTTk';
    private const FROM_EMAIL = 'contacto@sve.com.ar';
    private const FROM_NAME  = 'SVE Notificaciones';

    /**
     * Envío directo HTML.
     * $to puede ser string o array asociativo: ['mail@x.com' => 'Nombre'].
     */
    public static function send($to, string $subject, string $html, string $altText = ''): array
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = self::HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::USERNAME;
            $mail->Password   = self::PASSWORD;
            $mail->SMTPSecure = self::SECURE;
            $mail->Port       = self::PORT;

            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);

            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);

            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_int($email)) {
                        $mail->addAddress((string)$name);
                    } else {
                        $mail->addAddress((string)$email, (string)$name);
                    }
                }
            } else {
                $mail->addAddress((string)$to);
            }

            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $altText !== '' ? $altText : strip_tags($html);

            $mail->send();
            return ['ok' => true, 'error' => null];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
        }
    }

    /**
     * Renderiza una plantilla HTML en views/emails/{key}.html reemplazando {{placeholders}}.
     * $vars: ['id'=>..., 'mensaje'=>..., ...]
     */
    public static function sendTemplate(string $templateKey, $to, string $subject, array $vars = []): array
    {
        $file = __DIR__ . "/../../views/emails/{$templateKey}.html";
        if (!is_file($file)) {
            return ['ok' => false, 'error' => "Plantilla no encontrada: {$templateKey}"];
        }
        $html = file_get_contents($file) ?: '';

        // Reemplazo seguro de {{llave}}
        $html = preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/u', function ($m) use ($vars) {
            $key = $m[1];
            $val = self::dotGet($vars, $key);
            return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $html);

        $alt = isset($vars['alt']) ? (string)$vars['alt'] : '';
        return self::send($to, $subject, $html, $alt);
    }

    /** Lectura simple con notación "a.b.c" desde $vars */
    private static function dotGet(array $vars, string $path)
    {
        $parts = explode('.', $path);
        $val = $vars;
        foreach ($parts as $p) {
            if (is_array($val) && array_key_exists($p, $val)) {
                $val = $val[$p];
            } else {
                return '';
            }
        }
        return $val;
    }
}
