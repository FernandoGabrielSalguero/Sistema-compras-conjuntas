<?php

declare(strict_types=1);

namespace SVE\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/lib/PHPMailer.php';
require_once __DIR__ . '/lib/SMTP.php';
require_once __DIR__ . '/lib/Exception.php';

final class Mail
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

            $mail = self::baseMailer();
            $mail->Subject = 'Pedido realizado por ' . $coopNombre . ', para ' . $prodNombre . '.';
            $mail->Body    = $html;
            $mail->AltBody = 'Pedido realizado - ' . $coopNombre . ' / ' . $prodNombre;

            if (!empty($data['cooperativa_correo'])) {
                $mail->addAddress((string)$data['cooperativa_correo'], $coopNombre);
            }
            if (!empty($data['productor_correo'])) {
                $mail->addAddress((string)$data['productor_correo'], $prodNombre);
            }
            $mail->addAddress('lacruzg@coopsve.com', 'La Cruz');

            $mail->send();
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
