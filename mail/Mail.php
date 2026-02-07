<?php

declare(strict_types=1);

namespace SVE\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/lib/PHPMailer.php';
require_once __DIR__ . '/lib/SMTP.php';
require_once __DIR__ . '/lib/Exception.php';

final class Mail
{
    private const SEND_DELAY_US = 1000000; // 1s

    private static function baseMailer(): PHPMailer
    {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host       = \MAIL_HOST;
        $m->SMTPAuth   = true;
        $m->Username   = \MAIL_USER;
        $m->Password   = \MAIL_PASS;
        $m->SMTPSecure = \MAIL_SECURE;
        $m->Port       = \MAIL_PORT;
        $m->CharSet    = 'UTF-8';
        $m->setFrom(\MAIL_FROM, \MAIL_FROM_NAME);
        $m->addReplyTo(\MAIL_FROM, \MAIL_FROM_NAME);
        $m->isHTML(true);
        $m->Encoding   = 'base64';

        return $m;
    }

    private static function renderTemplate(string $path, string $content, string $title = ''): string
    {
        $tpl = is_file($path)
            ? (string)file_get_contents($path)
            : '<html><body style="font-family:Arial,sans-serif">{{content}}</body></html>';

        return str_replace(
            ['{{title}}', '{{content}}'],
            [$title, $content],
            $tpl
        );
    }

    private static function sendAndLog(PHPMailer $mail, string $tipo, string $template): bool
    {
        try {
            $ok = (bool)$mail->send();
            self::logEmail($mail, $tipo, $template, $ok, null);
            if (self::SEND_DELAY_US > 0) {
                usleep(self::SEND_DELAY_US);
            }
            return $ok;
        } catch (\Throwable $e) {
            self::logEmail($mail, $tipo, $template, false, $e->getMessage());
            if (self::SEND_DELAY_US > 0) {
                usleep(self::SEND_DELAY_US);
            }
            return false;
        }
    }

    private static function logEmail(PHPMailer $mail, string $tipo, string $template, bool $ok, ?string $error): void
    {
        try {
            $pdo = $GLOBALS['pdo'] ?? null;
            if (!$pdo instanceof \PDO) {
                error_log('[Mail] No PDO disponible para log_correos.');
                return;
            }

            $stmt = $pdo->prepare("
                INSERT INTO log_correos
                    (tipo, template, subject, from_email, from_name, reply_to, to_emails, cc_emails, bcc_emails, body_html, body_text, enviado_ok, error_msg, created_at)
                VALUES
                    (:tipo, :template, :subject, :from_email, :from_name, :reply_to, :to_emails, :cc_emails, :bcc_emails, :body_html, :body_text, :enviado_ok, :error_msg, NOW())
            ");

            $from = $mail->From ?? '';
            $fromName = $mail->FromName ?? '';
            $replyToArr = $mail->getReplyToAddresses();
            $replyTo = '';
            if (!empty($replyToArr)) {
                $first = reset($replyToArr);
                $replyTo = (string)($first[0] ?? '');
            }

            $stmt->execute([
                ':tipo' => $tipo,
                ':template' => $template,
                ':subject' => (string)($mail->Subject ?? ''),
                ':from_email' => (string)$from,
                ':from_name' => (string)$fromName,
                ':reply_to' => (string)$replyTo,
                ':to_emails' => json_encode($mail->getToAddresses(), JSON_UNESCAPED_UNICODE),
                ':cc_emails' => json_encode($mail->getCcAddresses(), JSON_UNESCAPED_UNICODE),
                ':bcc_emails' => json_encode($mail->getBccAddresses(), JSON_UNESCAPED_UNICODE),
                ':body_html' => (string)($mail->Body ?? ''),
                ':body_text' => (string)($mail->AltBody ?? ''),
                ':enviado_ok' => $ok ? 1 : 0,
                ':error_msg' => $error,
            ]);
        } catch (\Throwable $e) {
            error_log('[Mail] Error al registrar log_correos: ' . $e->getMessage());
        }
    }

    /**
     * Envia correo de compra realizada a cooperativa, productor y copia fija.
     * $data = [
     *   'cooperativa_nombre' => string,
     *   'cooperativa_correo' => ?string,
     *   'productor_nombre' => string,
     *   'productor_correo' => ?string,
     *   'operativo_nombre' => ?string,
     *   'items' => [ ['nombre'=>..., 'cantidad'=>float, 'unidad'=>string, 'precio'=>float, 'alicuota'=>float, 'subtotal'=>float, 'iva'=>float, 'total'=>float], ... ],
     *   'totales' => ['sin_iva'=>float,'iva'=>float,'con_iva'=>float],
     * ]
     * @return array{ok:bool, error?:string}
     */
    public static function enviarCompraRealizadaCooperativa(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/compra_realizada_cooperativa.html';

            $rows = '';
            foreach ((array)($data['items'] ?? []) as $it) {
                $rows .= sprintf(
                    '<tr>
                        <td>%s</td>
                        <td style="text-align:right;">%s</td>
                        <td>%s</td>
                        <td style="text-align:right;">$%0.2f</td>
                        <td style="text-align:right;">%0.2f%%</td>
                        <td style="text-align:right;">$%0.2f</td>
                        <td style="text-align:right;">$%0.2f</td>
                    </tr>',
                    htmlspecialchars((string)($it['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    number_format((float)($it['cantidad'] ?? 0), 2, ',', '.'),
                    htmlspecialchars((string)($it['unidad'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    (float)($it['precio'] ?? 0),
                    (float)($it['alicuota'] ?? 0),
                    (float)($it['subtotal'] ?? 0),
                    (float)($it['total'] ?? 0)
                );
            }
            if ($rows === '') {
                $rows = '<tr><td colspan="7" style="text-align:center;color:#6b7280;">Sin productos</td></tr>';
            }

            $coopNombre = (string)($data['cooperativa_nombre'] ?? 'Cooperativa');
            $prodNombre = (string)($data['productor_nombre'] ?? 'Productor');
            $opNombre = (string)($data['operativo_nombre'] ?? '');

            $content = sprintf(
                '<h2>Pedido realizado</h2>
                <p>La cooperativa <strong>%s</strong> realizó un pedido para el productor <strong>%s</strong>%s.</p>
                <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f3f4f6;">
                            <th style="text-align:left;">Producto</th>
                            <th style="text-align:right;">Cant.</th>
                            <th>Unidad</th>
                            <th style="text-align:right;">Precio</th>
                            <th style="text-align:right;">IVA</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>%s</tbody>
                </table>
                <p style="margin-top:12px;">
                <strong>Total s/IVA:</strong> $%0.2f<br/>
                <strong>IVA:</strong> $%0.2f<br/>
                <strong>Total c/IVA:</strong> $%0.2f
                </p>',
                htmlspecialchars($coopNombre, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($prodNombre, ENT_QUOTES, 'UTF-8'),
                $opNombre !== '' ? (' en el operativo <strong>' . htmlspecialchars($opNombre, ENT_QUOTES, 'UTF-8') . '</strong>') : '',
                $rows,
                (float)($data['totales']['sin_iva'] ?? 0),
                (float)($data['totales']['iva'] ?? 0),
                (float)($data['totales']['con_iva'] ?? 0)
            );

            $html = self::renderTemplate($tplPath, $content, 'Pedido realizado');

            $mailOk = true;

            // 1) Envio a cooperativa (asunto específico)
            if (!empty($data['cooperativa_correo'])) {
                $mailCoop = self::baseMailer();
                $mailCoop->Subject = 'Realizaste una compra para el productor ' . $prodNombre;
                $mailCoop->Body    = $html;
                $mailCoop->AltBody = 'Compra realizada para productor - ' . $prodNombre;
                $mailCoop->addAddress((string)$data['cooperativa_correo'], $coopNombre);
                $mailOk = $mailOk && self::sendAndLog($mailCoop, 'compra_realizada', 'compra_realizada_cooperativa.html');
            }

            // 2) Envio a copias fijas (asunto específico)
            $mailCopias = self::baseMailer();
            $mailCopias->Subject = 'La cooperativa ' . $coopNombre . ' realizo un pedido de compra conjunta para el productor ' . $prodNombre . '.';
            $mailCopias->Body    = $html;
            $mailCopias->AltBody = 'Pedido compra conjunta - ' . $coopNombre . ' / ' . $prodNombre;
            $mailCopias->addAddress('lacruzg@coopsve.com', 'La Cruz');
            $mailCopias->addAddress('fernandosalguero685@gmail.com', 'Fernando Salguero');
            $mailOk = $mailOk && self::sendAndLog($mailCopias, 'compra_realizada', 'compra_realizada_cooperativa.html');

            // 3) Envio al productor con asunto personalizado
            if (!empty($data['productor_correo'])) {
                $mailProd = self::baseMailer();
                $mailProd->Subject = 'Tu cooperativa ' . $coopNombre . ' realizo un pedido para vos, en el sistema de compra conjunta';
                $mailProd->Body    = $html;
                $mailProd->AltBody = 'Tu cooperativa realizo un pedido para vos - ' . $coopNombre;
                $mailProd->addAddress((string)$data['productor_correo'], $prodNombre);
                $mailOk = $mailOk && self::sendAndLog($mailProd, 'compra_realizada', 'compra_realizada_cooperativa.html');
            }

            return ['ok' => (bool)$mailOk];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
