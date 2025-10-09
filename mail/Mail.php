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
        $m->Encoding   = 'base64';

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

    /**
     * Envía correo “Solicitud de pulverización con dron”.
     * @param array $data
     *  [
     *    'solicitud_id'=>int,
     *    'productor'=>['nombre'=>string,'correo'=>string],
     *    'cooperativa'=>['nombre'=>string,'correo'=>?string],
     *    'superficie_ha'=>float,
     *    'forma_pago'=>string,
     *    'motivos'=>string[],
     *    'rangos'=>string[],
     *    'productos'=> [ ['patologia'=>string,'fuente'=>'sve'|'yo','detalle'=>string], ... ],
     *    'direccion'=> ['provincia'=>?string,'localidad'=>?string,'calle'=>?string,'numero'=>?string],
     *    'ubicacion'=> ['en_finca'=>'si'|'no', 'lat'=>?string,'lng'=>?string,'acc'=>?string,'timestamp'=>?string],
     *    'costos'=> ['moneda'=>string,'base'=>float,'productos'=>float,'total'=>float,'costo_ha'=>float],
     *  ]
     * @return array{ok:bool,error?:string}
     */
    public static function enviarSolicitudDron(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/dron_solicitud.html';
            $tpl = is_file($tplPath) ? file_get_contents($tplPath) : '<html><body style="font-family:Arial,sans-serif">{CONTENT}</body></html>';

            $prodRows = '';
            foreach ((array)($data['productos'] ?? []) as $p) {
                $prodRows .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
                    htmlspecialchars((string)($p['patologia'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['fuente'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['detalle'] ?? ''), ENT_QUOTES, 'UTF-8')
                );
            }
            if ($prodRows === '') {
                $prodRows = '<tr><td colspan="3" style="text-align:center;color:#6b7280;">Sin productos</td></tr>';
            }

            $motivos = implode(', ', array_map(fn($m) => htmlspecialchars((string)$m, ENT_QUOTES, 'UTF-8'), (array)($data['motivos'] ?? [])));
            $rangos  = implode(', ', array_map(fn($r) => htmlspecialchars((string)$r, ENT_QUOTES, 'UTF-8'), (array)($data['rangos'] ?? [])));
            $dir     = $data['direccion'] ?? [];
            $dirText = trim(
                (($dir['calle'] ?? '') . ' ' . ($dir['numero'] ?? '')) . ', ' .
                    ($dir['localidad'] ?? '') . ', ' . ($dir['provincia'] ?? ''),
                " ,"
            );

            $ubi     = $data['ubicacion'] ?? [];
            $ubiText = sprintf(
                'En finca: %s%s',
                (($ubi['en_finca'] ?? '') === 'si' ? 'Sí' : 'No'),
                (!empty($ubi['lat']) && !empty($ubi['lng'])) ? sprintf(' — (%.6f, %.6f)', (float)$ubi['lat'], (float)$ubi['lng']) : ''
            );

            $costos = $data['costos'] ?? ['moneda' => 'Pesos', 'base' => 0, 'productos' => 0, 'total' => 0, 'costo_ha' => 0];

            $content = sprintf(
                '<h2 style="margin:0 0 8px 0;">Nueva solicitud de pulverización con dron</h2>
                 <p style="margin:0 0 14px 0;color:#374151;">ID solicitud: <strong>#%d</strong></p>

                 <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;margin-bottom:12px;">
                   <tbody>
                     <tr><td style="width:35%%;background:#f9fafb;">Productor</td><td><strong>%s</strong> &lt;%s&gt;</td></tr>
                     <tr><td style="background:#f9fafb;">Cooperativa</td><td>%s</td></tr>
                     <tr><td style="background:#f9fafb;">Superficie</td><td>%0.2f ha</td></tr>
                     <tr><td style="background:#f9fafb;">Forma de pago</td><td>%s</td></tr>
                     <tr><td style="background:#f9fafb;">Motivo(s)</td><td>%s</td></tr>
                     <tr><td style="background:#f9fafb;">Rango deseado</td><td>%s</td></tr>
                     <tr><td style="background:#f9fafb;">Dirección</td><td>%s</td></tr>
                     <tr><td style="background:#f9fafb;">Ubicación</td><td>%s</td></tr>
                   </tbody>
                 </table>

                 <h3 style="margin:14px 0 6px 0;">Productos</h3>
                 <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                   <thead>
                     <tr style="background:#f3f4f6;">
                       <th style="text-align:left;">Patología</th>
                       <th style="text-align:left;">Fuente</th>
                       <th style="text-align:left;">Detalle</th>
                     </tr>
                   </thead>
                   <tbody>%s</tbody>
                 </table>

                 <h3 style="margin:16px 0 6px 0;">Costo estimado</h3>
                 <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                   <tbody>
                     <tr><td style="width:35%%;background:#f9fafb;">Servicio base</td><td>%s %0.2f</td></tr>
                     <tr><td style="background:#f9fafb;">Productos SVE</td><td>%s %0.2f</td></tr>
                     <tr><td style="background:#f9fafb;"><strong>Total</strong></td><td><strong>%s %0.2f</strong></td></tr>
                   </tbody>
                 </table>',
                (int)($data['solicitud_id'] ?? 0),
                htmlspecialchars((string)($data['productor']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)($data['productor']['correo'] ?? ''), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)($data['cooperativa']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
                (float)($data['superficie_ha'] ?? 0),
                htmlspecialchars((string)($data['forma_pago'] ?? ''), ENT_QUOTES, 'UTF-8'),
                $motivos ?: '—',
                $rangos ?: '—',
                htmlspecialchars($dirText ?: '—', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($ubiText, ENT_QUOTES, 'UTF-8'),
                $prodRows,
                htmlspecialchars((string)$costos['moneda'], ENT_QUOTES, 'UTF-8'),
                (float)$costos['base'],
                htmlspecialchars((string)$costos['moneda'], ENT_QUOTES, 'UTF-8'),
                (float)$costos['productos'],
                htmlspecialchars((string)$costos['moneda'], ENT_QUOTES, 'UTF-8'),
                (float)$costos['total']
            );

            $html = str_replace('{CONTENT}', $content, $tpl);

            $mail = self::baseMailer();
            $mail->Subject = '🟣 SVE: Nueva solicitud de pulverización con dron';
            $mail->Body    = $html;
            $mail->AltBody = 'Nueva solicitud de dron - ID #' . (int)($data['solicitud_id'] ?? 0);

            // Destinatarios:
            // 1) Siempre la casilla de drones
            $mail->addAddress('dronesvecoop@gmail.com', 'Drones SVE');

            // 2) Productor (si hay correo)
            if (!empty($data['productor']['correo'])) {
                $mail->addAddress((string)$data['productor']['correo'], (string)($data['productor']['nombre'] ?? ''));
            }

            // 3) Cooperativa (si hay correo)
            if (!empty($data['cooperativa']['correo'])) {
                $mail->addAddress((string)$data['cooperativa']['correo'], (string)($data['cooperativa']['nombre'] ?? ''));
            }

            $mail->send();
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envía correo “Solicitud de dron ACTUALIZADA” con el resumen de cambios.
     * $data = [
     *   'solicitud_id' => int,
     *   'estado_anterior' => ?string,
     *   'estado_actual' => ?string,
     *   'productor' => ['nombre'=>?string,'correo'=>?string],
     *   'cooperativas' => [ ['usuario'=>?string,'correo'=>?string], ... ],
     *   'cambios' => [ ['campo'=>string,'antes'=>string,'despues'=>string], ... ],
     *   'costos' => ['moneda'=>string,'base_total'=>float,'productos_total'=>float,'total'=>float]
     * ]
     * @return array{ok:bool,error?:string}
     */
    public static function enviarSolicitudDronActualizada(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/dron_actualizada.html';
            $tpl = is_file($tplPath) ? file_get_contents($tplPath) : '<html><body style="font-family:Arial,sans-serif">{CONTENT}</body></html>';

            $rows = '';
            foreach ((array)($data['cambios'] ?? []) as $c) {
                $rows .= sprintf(
                    '<tr>
                        <td style="padding:8px;border-bottom:1px solid #eee;">%s</td>
                        <td style="padding:8px;border-bottom:1px solid #eee;color:#6b7280;">%s</td>
                        <td style="padding:8px;border-bottom:1px solid #eee;"><strong>%s</strong></td>
                    </tr>',
                    htmlspecialchars((string)$c['campo'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)$c['antes'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)$c['despues'], ENT_QUOTES, 'UTF-8')
                );
            }
            if ($rows === '') {
                $rows = '<tr><td colspan="3" style="padding:8px;text-align:center;color:#6b7280;">Sin cambios detectados</td></tr>';
            }

            $costos = $data['costos'] ?? ['moneda' => 'Pesos', 'base_total' => 0, 'productos_total' => 0, 'total' => 0];

            $content = sprintf(
                '<h2 style="margin:0 0 6px 0;">Solicitud de dron actualizada</h2>
                 <p style="margin:0 0 12px 0;color:#374151;">ID solicitud: <strong>#%d</strong></p>
                 <p style="margin:0 0 12px 0;color:#374151;">
                   Estado: <span style="background:#eef;padding:2px 8px;border-radius:999px;">%s</span>
                   &nbsp;→&nbsp;
                   <span style="background:#dcfce7;padding:2px 8px;border-radius:999px;">%s</span>
                 </p>

                 <h3 style="margin:12px 0 6px 0;">Resumen de cambios</h3>
                 <table cellpadding="0" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                   <thead>
                     <tr style="background:#f3f4f6;">
                       <th style="text-align:left;padding:8px;">Campo</th>
                       <th style="text-align:left;padding:8px;">Antes</th>
                       <th style="text-align:left;padding:8px;">Después</th>
                     </tr>
                   </thead>
                   <tbody>%s</tbody>
                 </table>

                 <h3 style="margin:16px 0 6px 0;">Costos (snapshot)</h3>
                 <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                   <tbody>
                     <tr><td style="width:35%%;background:#f9fafb;">Base total</td><td>%s %0.2f</td></tr>
                     <tr><td style="background:#f9fafb;">Productos total</td><td>%s %0.2f</td></tr>
                     <tr><td style="background:#f9fafb;"><strong>Total</strong></td><td><strong>%s %0.2f</strong></td></tr>
                   </tbody>
                 </table>',
                (int)($data['solicitud_id'] ?? 0),
                htmlspecialchars((string)($data['estado_anterior'] ?? '—'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)($data['estado_actual'] ?? '—'), ENT_QUOTES, 'UTF-8'),
                $rows,
                htmlspecialchars((string)($costos['moneda'] ?? 'Pesos'), ENT_QUOTES, 'UTF-8'),
                (float)($costos['base_total'] ?? 0),
                htmlspecialchars((string)($costos['moneda'] ?? 'Pesos'), ENT_QUOTES, 'UTF-8'),
                (float)($costos['productos_total'] ?? 0),
                htmlspecialchars((string)($costos['moneda'] ?? 'Pesos'), ENT_QUOTES, 'UTF-8'),
                (float)($costos['total'] ?? 0)
            );

            $html = str_replace('{CONTENT}', $content, $tpl);

            $mail = self::baseMailer();
            $mail->Subject = '🟣 SVE: Solicitud de dron actualizada';
            $mail->Body    = $html;
            $mail->AltBody = 'Solicitud actualizada - ID #' . (int)($data['solicitud_id'] ?? 0);

            // Destinatarios: siempre casilla de drones
            // $mail->addAddress('dronesvecoop@gmail.com', 'Drones SVE');
            $mail->addAddress('fernandosalguero685@gmail.com', 'Drones SVE');

            // Productor (si hay)
            $pCorreo = (string)($data['productor']['correo'] ?? '');
            $pNombre = (string)($data['productor']['nombre'] ?? '');
            if ($pCorreo !== '') {
                $mail->addAddress($pCorreo, $pNombre);
            }

            // Cooperativas (si hay correo). Evitar duplicados.
            $added = [];
            foreach ((array)($data['cooperativas'] ?? []) as $c) {
                $cc = trim((string)($c['correo'] ?? ''));
                $nn = (string)($c['usuario'] ?? '');
                if ($cc !== '' && !isset($added[$cc])) {
                    $mail->addAddress($cc, $nn);
                    $added[$cc] = true;
                }
            }

            $mail->send();
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
