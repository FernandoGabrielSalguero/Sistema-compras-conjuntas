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

    private static function formatDateShort(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $s = (string)$value;
        $soloFecha = explode(' ', $s)[0];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $soloFecha) === 1) {
            [$y, $m, $d] = explode('-', $soloFecha);
            return $d . '/' . $m . '/' . $y;
        }

        return $s;
    }

    private static function normalizarSeguroFlete(?string $valor): string
    {
        $raw = strtolower(trim((string)$valor));
        if ($raw === '' || $raw === 'sin definir' || $raw === 'sin_definir') {
            return 'Sin definir';
        }
        if ($raw === 'si' || $raw === '1') {
            return 'Si';
        }
        if ($raw === 'no' || $raw === '0') {
            return 'No';
        }
        return 'Sin definir';
    }

    private static function buildDroneSolicitudContent(array $data, bool $includeCoopExtra): string
    {
        $prodNombre = (string)($data['productor']['nombre'] ?? '');
        $prodCorreo = (string)($data['productor']['correo'] ?? '');
        $coopNombre = (string)($data['cooperativa']['nombre'] ?? '');

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

        $costos = $data['costos'] ?? ['moneda' => 'Pesos', 'base' => 0, 'productos' => 0, 'total' => 0];

        $coopExtra = '';
        if ($includeCoopExtra) {
            $texto = trim((string)($data['coop_texto_extra'] ?? ''));
            $ctaApprove = (string)($data['cta_approve_url'] ?? '');
            $ctaDecline = (string)($data['cta_decline_url'] ?? '');

            $nota = '<p style="margin:12px 0 0 0;color:#6b7280;font-size:13px;">'
                . 'Los botones tienen una validez de 24hs. '
                . 'Una vez seleccionada una opcion, si deseas cambiar la decision debes ingresar al sistema.'
                . '</p>';

            $botones = '';
            if ($ctaApprove !== '' && $ctaDecline !== '') {
                $botones = sprintf(
                    '<div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap;">
                        <a href="%1$s" style="background:#10b981;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;display:inline-block;font-weight:600;">Autorizar</a>
                        <a href="%2$s" style="background:#ef4444;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;display:inline-block;font-weight:600;">Denegar</a>
                    </div>',
                    htmlspecialchars($ctaApprove, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($ctaDecline, ENT_QUOTES, 'UTF-8')
                );
            }

            if ($texto !== '' || $botones !== '') {
                $coopExtra = '<h3 style="margin:16px 0 6px 0;">Autorizacion requerida</h3>'
                    . '<div style="color:#111;line-height:1.5;">' . nl2br(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8')) . '</div>'
                    . $botones
                    . $nota;
            }
        }

        return sprintf(
            '<h2 style="margin:0 0 8px 0;">Solicitud de servicio de drones</h2>
            <p style="margin:0 0 14px 0;color:#374151;">ID solicitud: <strong>#%d</strong></p>

            <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;margin-bottom:12px;">
            <tbody>
                <tr><td style="width:35%%;background:#f9fafb;">Productor</td><td><strong>%s</strong> &lt;%s&gt;</td></tr>
                <tr><td style="background:#f9fafb;">Cooperativa</td><td>%s</td></tr>
                <tr><td style="background:#f9fafb;">Superficie</td><td>%0.2f ha</td></tr>
                <tr><td style="background:#f9fafb;">Metodo de pago</td><td>%s</td></tr>
                <tr><td style="background:#f9fafb;">Motivo(s)</td><td>%s</td></tr>
                <tr><td style="background:#f9fafb;">Rango deseado</td><td>%s</td></tr>
                <tr><td style="background:#f9fafb;">Direccion</td><td>%s</td></tr>
                <tr><td style="background:#f9fafb;">Ubicacion</td><td>%s</td></tr>
            </tbody>
            </table>

            <h3 style="margin:14px 0 6px 0;">Productos</h3>
            <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f3f4f6;">
                <th style="text-align:left;">Patologia</th>
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
            </table>
            %s',
            (int)($data['solicitud_id'] ?? 0),
            htmlspecialchars($prodNombre, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($prodCorreo, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($coopNombre, ENT_QUOTES, 'UTF-8'),
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
            (float)$costos['total'],
            $coopExtra
        );
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

    private static function sendAndLog(PHPMailer $mail, string $tipo, string $template, array $meta = []): bool
    {
        try {
            $ok = (bool)$mail->send();
            $errInfo = (!$ok && property_exists($mail, 'ErrorInfo')) ? (string)$mail->ErrorInfo : null;
            self::logEmail($mail, $tipo, $template, $meta, $ok, $errInfo);
            if (self::SEND_DELAY_US > 0) {
                usleep(self::SEND_DELAY_US);
            }
            return $ok;
        } catch (\Throwable $e) {
            $errInfo = property_exists($mail, 'ErrorInfo') ? (string)$mail->ErrorInfo : null;
            $err = $errInfo !== '' ? ($e->getMessage() . ' | ' . $errInfo) : $e->getMessage();
            self::logEmail($mail, $tipo, $template, $meta, false, $err);
            if (self::SEND_DELAY_US > 0) {
                usleep(self::SEND_DELAY_US);
            }
            return false;
        }
    }

    private static function logEmail(PHPMailer $mail, string $tipo, string $template, array $meta, bool $ok, ?string $error): void
    {
        try {
            $pdo = $GLOBALS['pdo'] ?? null;
            if (!$pdo instanceof \PDO) {
                error_log('[Mail] No PDO disponible para log_correos.');
                return;
            }

            $stmt = $pdo->prepare("
                INSERT INTO log_correos
                    (tipo, template, contrato_id, cooperativa_id_real, correo, enviado_por, subject, from_email, from_name, reply_to, to_emails, cc_emails, bcc_emails, body_html, body_text, enviado_ok, error_msg, created_at)
                VALUES
                    (:tipo, :template, :contrato_id, :cooperativa_id_real, :correo, :enviado_por, :subject, :from_email, :from_name, :reply_to, :to_emails, :cc_emails, :bcc_emails, :body_html, :body_text, :enviado_ok, :error_msg, NOW())
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
                ':contrato_id' => $meta['contrato_id'] ?? null,
                ':cooperativa_id_real' => $meta['cooperativa_id_real'] ?? null,
                ':correo' => $meta['correo'] ?? null,
                ':enviado_por' => $meta['enviado_por'] ?? null,
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
     * Envia correo de cierre de operativo de Cosecha Mecanica.
     * $data = [
     *   'cooperativa_nombre' => string,
     *   'cooperativa_correo' => string,
     *   'cooperativa_id_real' => ?string,
     *   'operativo' => [id,nombre,fecha_apertura,fecha_cierre,descripcion,estado],
     *   'participaciones' => [ ['productor'=>..., 'finca_id'=>..., 'superficie'=>..., 'variedad'=>..., 'prod_estimada'=>..., 'fecha_estimada'=>..., 'km_finca'=>..., 'flete'=>..., 'seguro_flete'=>...], ... ],
     *   'firma_fecha' => ?string,
     *   'enviado_por' => ?string,
     * ]
     * @return array{ok:bool, error?:string}
     */
    public static function enviarCierreCosechaMecanica(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/cosecha_cierre_operativo.html';

            $op = $data['operativo'] ?? [];
            $participaciones = $data['participaciones'] ?? [];
            $nombreCoop = (string)($data['cooperativa_nombre'] ?? 'Cooperativa');

            $descripcionRaw = (string)($op['descripcion'] ?? '');
            $descripcionHtml = trim($descripcionRaw);
            if ($descripcionHtml !== '') {
                $descripcionHtml = html_entity_decode($descripcionHtml, ENT_QUOTES, 'UTF-8');
                $descripcionHtml = strip_tags($descripcionHtml, '<p><br><strong><b><em><i><u><ul><ol><li><span><div><table><thead><tbody><tr><th><td>');
            } else {
                $descripcionHtml = '-';
            }

            $fechaApertura = self::formatDateShort($op['fecha_apertura'] ?? null);
            $fechaCierre = self::formatDateShort($op['fecha_cierre'] ?? null);
            $fechaFirma = self::formatDateShort($data['firma_fecha'] ?? null);
            $estado = (string)($op['estado'] ?? 'cerrado');

            $rows = '';
            foreach ((array)$participaciones as $p) {
                $rows .= sprintf(
                    '<tr>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>',
                    htmlspecialchars((string)($p['productor'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['finca_id'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['superficie'] ?? 0), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['variedad'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['prod_estimada'] ?? 0), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(self::formatDateShort($p['fecha_estimada'] ?? null), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['km_finca'] ?? 0), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)($p['flete'] ?? 0), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(self::normalizarSeguroFlete($p['seguro_flete'] ?? null), ENT_QUOTES, 'UTF-8')
                );
            }
            if ($rows === '') {
                $rows = '<tr><td colspan="9" style="text-align:center;color:#6b7280;">Sin productores inscriptos</td></tr>';
            }

            $content = sprintf(
                '<h2>Cierre de operativo - Cosecha Mecanica</h2>
                <p>Hola %s, el operativo ya fue cerrado. Este es el detalle del contrato:</p>
                <h3 style="font-size:14px;margin:16px 0 8px 0;">Datos del contrato</h3>
                <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                    <tbody>
                        <tr><td style="width:32%%;background:#f9fafb;">Operativo</td><td>%s</td></tr>
                        <tr><td style="background:#f9fafb;">Apertura</td><td>%s</td></tr>
                        <tr><td style="background:#f9fafb;">Cierre</td><td>%s</td></tr>
                        <tr><td style="background:#f9fafb;">Estado</td><td>%s</td></tr>
                        <tr><td style="background:#f9fafb;">Descripcion</td><td style="white-space:pre-wrap;">%s</td></tr>
                        <tr><td style="background:#f9fafb;">Contrato firmado</td><td>Si</td></tr>
                        <tr><td style="background:#f9fafb;">Fecha firma</td><td>%s</td></tr>
                    </tbody>
                </table>
                <h3 style="font-size:14px;margin:16px 0 8px 0;">Anexo 1 - Productores inscriptos</h3>
                <table cellpadding="8" cellspacing="0" border="0" style="width:100%%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f3f4f6;">
                            <th style="text-align:left;">Productor</th>
                            <th style="text-align:left;">Finca ID</th>
                            <th style="text-align:left;">Superficie</th>
                            <th style="text-align:left;">Variedad</th>
                            <th style="text-align:left;">Prod. estimada</th>
                            <th style="text-align:left;">Fecha estimada</th>
                            <th style="text-align:left;">KM finca</th>
                            <th style="text-align:left;">Flete</th>
                            <th style="text-align:left;">Seguro flete</th>
                        </tr>
                    </thead>
                    <tbody>%s</tbody>
                </table>',
                htmlspecialchars($nombreCoop, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)($op['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($fechaApertura, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($fechaCierre, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'),
                $descripcionHtml,
                htmlspecialchars($fechaFirma, ENT_QUOTES, 'UTF-8'),
                $rows
            );

            $html = self::renderTemplate($tplPath, $content, 'Cierre operativo Cosecha Mecanica');

            $mail = self::baseMailer();
            $mail->Subject = 'Cierre de operativo de Cosecha Mecanica';
            $mail->Body    = $html;
            $mail->AltBody = 'Cierre de operativo de Cosecha Mecanica - ' . (string)($op['nombre'] ?? '');

            $correo = (string)($data['cooperativa_correo'] ?? '');
            if ($correo === '') {
                return ['ok' => false, 'error' => 'Correo de cooperativa no valido.'];
            }
            $mail->addAddress($correo, $nombreCoop);

            $meta = [
                'contrato_id' => (int)($op['id'] ?? 0),
                'cooperativa_id_real' => $data['cooperativa_id_real'] ?? null,
                'correo' => $correo,
                'enviado_por' => $data['enviado_por'] ?? null,
            ];

            $tipoLog = (string)($data['tipo_log'] ?? 'cierre');
            $ok = self::sendAndLog($mail, $tipoLog, 'cosecha_cierre_operativo.html', $meta);
            return ['ok' => (bool)$ok];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envia correo de solicitud de dron a correos fijos y copia al productor.
     * @return array{ok:bool, error?:string}
     */
    public static function enviarSolicitudDronProductor(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/drone_solicitud_productor.html';
            $content = self::buildDroneSolicitudContent($data, false);
            $html = self::renderTemplate($tplPath, $content, 'Solicitud de servicio de drones');

            $prodNombre = (string)($data['productor']['nombre'] ?? 'Productor');
            $subject = 'El productor ' . $prodNombre . ' solicito un servicio de drone.';

            $mail = self::baseMailer();
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $subject;

            $mail->addAddress('dronesvecoop@gmail.com', 'Drones SVE');
            $mail->addAddress('fernandosalguero685@gmail.com', 'Fernando Salguero');
            if (!empty($data['productor']['correo'])) {
                $mail->addAddress((string)$data['productor']['correo'], $prodNombre);
            }

            $meta = [
                'contrato_id' => (int)($data['solicitud_id'] ?? 0),
                'correo' => (string)($data['productor']['correo'] ?? ''),
                'enviado_por' => 'productor',
            ];

            $ok = self::sendAndLog($mail, 'dron_solicitud_productor', 'drone_solicitud_productor.html', $meta);
            return ['ok' => (bool)$ok];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envia correo de autorizacion a cooperativa cuando pago es descuento por cooperativa.
     * @return array{ok:bool, error?:string}
     */
    public static function enviarSolicitudDronAutorizacionCoop(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/autorizacion_drone/dron_autorizacion_cooperativa.html';
            $content = self::buildDroneSolicitudContent($data, true);
            $html = self::renderTemplate($tplPath, $content, 'Autorizacion cooperativa');

            $prodNombre = (string)($data['productor']['nombre'] ?? 'Productor');
            $subject = 'El productor ' . $prodNombre . ' solicito un servicio de drones.';

            $correo = (string)($data['cooperativa']['correo'] ?? '');
            if ($correo === '') {
                return ['ok' => false, 'error' => 'Correo de cooperativa no valido.'];
            }

            $mail = self::baseMailer();
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $subject;
            $mail->addAddress($correo, (string)($data['cooperativa']['nombre'] ?? 'Cooperativa'));

            $meta = [
                'contrato_id' => (int)($data['solicitud_id'] ?? 0),
                'cooperativa_id_real' => $data['cooperativa']['id_real'] ?? null,
                'correo' => $correo,
                'enviado_por' => 'productor',
            ];

            $ok = self::sendAndLog($mail, 'dron_autorizacion_coop', 'donde_autorizacion_cooperativa.html', $meta);
            return ['ok' => (bool)$ok];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
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

    /**
     * Envia correo de compra realizada desde panel SVE.
     * Misma data que enviarCompraRealizadaCooperativa(), con template SVE.
     * @return array{ok:bool, error?:string}
     */
    public static function enviarCompraRealizadaSVE(array $data): array
    {
        try {
            $tplPath = __DIR__ . '/template/compra_realizada_sve.html';

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

            // 1) Envio a cooperativa
            if (!empty($data['cooperativa_correo'])) {
                $mailCoop = self::baseMailer();
                $mailCoop->Subject = 'Realizaste una compra para el productor ' . $prodNombre;
                $mailCoop->Body    = $html;
                $mailCoop->AltBody = 'Compra realizada para productor - ' . $prodNombre;
                $mailCoop->addAddress((string)$data['cooperativa_correo'], $coopNombre);
                $mailOk = $mailOk && self::sendAndLog($mailCoop, 'compra_realizada_sve', 'compra_realizada_sve.html');
            }

            // 2) Envio a copias fijas
            $mailCopias = self::baseMailer();
            $mailCopias->Subject = 'La cooperativa ' . $coopNombre . ' realizo un pedido de compra conjunta para el productor ' . $prodNombre . '.';
            $mailCopias->Body    = $html;
            $mailCopias->AltBody = 'Pedido compra conjunta - ' . $coopNombre . ' / ' . $prodNombre;
            $mailCopias->addAddress('lacruzg@coopsve.com', 'La Cruz');
            $mailCopias->addAddress('fernandosalguero685@gmail.com', 'Fernando Salguero');
            $mailOk = $mailOk && self::sendAndLog($mailCopias, 'compra_realizada_sve', 'compra_realizada_sve.html');

            // 3) Envio al productor
            if (!empty($data['productor_correo'])) {
                $mailProd = self::baseMailer();
                $mailProd->Subject = 'Tu cooperativa ' . $coopNombre . ' realizo un pedido para vos, en el sistema de compra conjunta';
                $mailProd->Body    = $html;
                $mailProd->AltBody = 'Tu cooperativa realizo un pedido para vos - ' . $coopNombre;
                $mailProd->addAddress((string)$data['productor_correo'], $prodNombre);
                $mailOk = $mailOk && self::sendAndLog($mailProd, 'compra_realizada_sve', 'compra_realizada_sve.html');
            }

            return ['ok' => (bool)$mailOk];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
