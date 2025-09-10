<?php

declare(strict_types=1);

namespace SVE\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/lib/PHPMailer.php';
require_once __DIR__ . '/lib/SMTP.php';
require_once __DIR__ . '/lib/Exception.php';

final class Maill
{
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
        return $m;
    }

    /**
     * Envía correo “Pedido creado”.
     * $data = [
     *   'cooperativa_nombre' => string,
     *   'cooperativa_correo' => string|null,
     *   'operativo_nombre'   => string,
     *   'items' => [ ['nombre'=>..., 'cantidad'=>float, 'unidad'=>string, 'precio'=>float, 'alicuota'=>float, 'subtotal'=>float, 'iva'=>float, 'total'=>float], ... ],
     *   'totales' => ['sin_iva'=>float,'iva'=>float,'con_iva'=>float],
     * ]
     * @return array{ok:bool, error?:string}
     */
    public static function enviarPedidoCreado(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/pedido_creado.html';
            $tpl = is_file($tplPath) ? file_get_contents($tplPath) : '<html><body style="font-family:Arial,sans-serif">{CONTENT}</body></html>';

            $rows = '';
            foreach ($data['items'] as $it) {
                $rows .= sprintf(
                    '<tr><td>%s</td><td style="text-align:right;">%s</td><td>%s</td><td style="text-align:right;">$%0.2f</td><td style="text-align:right;">%0.2f%%</td><td style="text-align:right;">$%0.2f</td><td style="text-align:right;">$%0.2f</td></tr>',
                    htmlspecialchars((string)$it['nombre'], ENT_QUOTES, 'UTF-8'),
                    number_format((float)$it['cantidad'], 2, ',', '.'),
                    htmlspecialchars((string)($it['unidad'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    (float)$it['precio'],
                    (float)$it['alicuota'],
                    (float)$it['subtotal'],
                    (float)$it['total']
                );
            }

            $content = sprintf(
                '<h2>Nuevo pedido en Mercado Digital</h2>
                <p>La cooperativa <strong>%s</strong> generó un pedido para el operativo <strong>%s</strong>.</p>
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
                htmlspecialchars((string)$data['cooperativa_nombre'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)$data['operativo_nombre'], ENT_QUOTES, 'UTF-8'),
                $rows,
                (float)$data['totales']['sin_iva'],
                (float)$data['totales']['iva'],
                (float)$data['totales']['con_iva']
            );

            $html = str_replace('{CONTENT}', $content, $tpl);

            $mail = self::baseMailer();
            $mail->Subject = '🟣 SVE: Nuevo pedido creado';
            $mail->Body    = $html;
            $mail->AltBody = 'Nuevo pedido creado - ' . $data['cooperativa_nombre'] . ' - ' . $data['operativo_nombre'];

            // Destinatarios
            if (!empty($data['cooperativa_correo'])) {
                $mail->addAddress((string)$data['cooperativa_correo']);
            }
            $mail->addAddress('lacruzg@coopsve.com', 'La Cruz');

            $mail->send();
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
