<?php

declare(strict_types=1);

namespace SVE\Brevo;

/**
 * Mailer específico para solicitudes de dron usando Brevo.
 *
 * Espera el mismo $mailPayload que arma prod_dronesController.php, por ejemplo:
 * [
 *   'solicitud_id'  => int,
 *   'productor'     => ['nombre' => string, 'correo' => string],
 *   'cooperativa'   => ['nombre' => string, 'correo' => string],
 *   'superficie_ha' => float,
 *   'forma_pago'    => string,
 *   'motivos'       => string[],
 *   'rangos'        => string[],
 *   'productos'     => [
 *       [
 *          'patologia' => string,
 *          'fuente'    => 'sve'|'yo',
 *          'detalle'   => string,
 *       ],
 *       ...
 *   ],
 *   'direccion'     => ['provincia'=>..., 'localidad'=>..., 'calle'=>..., 'numero'=>...],
 *   'ubicacion'     => ['en_finca'=>..., 'lat'=>..., 'lng'=>..., 'acc'=>..., 'timestamp'=>...],
 *   'costos'        => ['moneda'=>..., 'base'=>..., 'productos'=>..., 'total'=>..., 'costo_ha'=>...],
 *   'pago_por_coop' => bool,
 *   'cta_url'       => string,
 *   'coop_texto_extra' => string,
 * ]
 */
class DronSolicitudMailer
{
    /**
     * Envía el correo de solicitud de dron a través de Brevo.
     *
     * @param array $payload
     * @return array ['ok' => bool, 'error' => string|null]
     */
    public static function enviarSolicitudDron(array $payload): array
    {
        // 1) API key de Brevo
        $apiKey = \defined('BREVO_API_KEY') ? BREVO_API_KEY : (\getenv('BREVO_API_KEY') ?: '');
        if (!$apiKey) {
            return [
                'ok'    => false,
                'error' => 'BREVO_API_KEY no configurada en config.php / entorno.',
            ];
        }

        // 2) Remitente (reutilizamos MAIL_FROM / MAIL_FROM_NAME)
        $fromEmail = \defined('MAIL_FROM') ? MAIL_FROM : 'no-reply@sve.com.ar';
        $fromName  = \defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SVE Notificaciones';

        // 3) Destinatarios
        $solicitudId = (int)($payload['solicitud_id'] ?? 0);

        $productor   = (array)($payload['productor'] ?? []);
        $coop        = (array)($payload['cooperativa'] ?? []);

        $to = [];
        $cc = [];

        if (!empty($productor['correo'])) {
            $to[] = [
                'email' => (string)$productor['correo'],
                'name'  => (string)($productor['nombre'] ?? ''),
            ];
        }

        if (!empty($coop['correo'])) {
            $cc[] = [
                'email' => (string)$coop['correo'],
                'name'  => (string)($coop['nombre'] ?? ''),
            ];
        }

        if (!$to && !$cc) {
            return [
                'ok'    => false,
                'error' => 'No hay destinatarios válidos para enviar el correo.',
            ];
        }

        // 4) Asunto
        $subject = 'Nueva solicitud de servicio de dron';
        if ($solicitudId > 0) {
            $subject .= ' #' . $solicitudId;
        }

        // 5) Cuerpo HTML
        $htmlContent = self::buildHtmlContent($payload);

        // 6) Armar body para Brevo
        $body = [
            'sender' => [
                'email' => $fromEmail,
                'name'  => $fromName,
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ];

        // Si hay destinatarios principales, van en "to". Si no, usamos cc como to.
        if ($to) {
            $body['to'] = $to;
            if ($cc) {
                $body['cc'] = $cc;
            }
        } else {
            $body['to'] = $cc;
        }

        // 7) Enviar vía cURL
        if (!\function_exists('curl_init')) {
            return [
                'ok'    => false,
                'error' => 'cURL no está disponible en el servidor (requerido para Brevo).',
            ];
        }

        $ch = \curl_init('https://api.brevo.com/v3/smtp/email');
        \curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => \json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = \curl_exec($ch);
        $httpCode = (int)\curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = \curl_error($ch);
        \curl_close($ch);

        if ($response === false) {
            return [
                'ok'    => false,
                'error' => 'Error cURL al enviar correo con Brevo: ' . $curlErr,
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'ok'    => false,
                'error' => 'Error HTTP ' . $httpCode . ' al enviar correo con Brevo: ' . $response,
            ];
        }

        return [
            'ok'    => true,
            'error' => null,
        ];
    }

    /**
     * Construye el HTML del correo usando el payload que arma prod_dronesController.php.
     */
    private static function buildHtmlContent(array $p): string
    {
        $id          = (int)($p['solicitud_id'] ?? 0);
        $productor   = (array)($p['productor'] ?? []);
        $cooperativa = (array)($p['cooperativa'] ?? []);
        $costos      = (array)($p['costos'] ?? []);
        $motivos     = (array)($p['motivos'] ?? []);
        $rangos      = (array)($p['rangos'] ?? []);
        $productos   = (array)($p['productos'] ?? []);
        $direccion   = (array)($p['direccion'] ?? []);
        $ubicacion   = (array)($p['ubicacion'] ?? []);

        $superficie  = (float)($p['superficie_ha'] ?? 0);
        $formaPago   = (string)($p['forma_pago'] ?? '');
        $moneda      = (string)($costos['moneda'] ?? '');
        $base        = (float)($costos['base'] ?? 0);
        $prodCost    = (float)($costos['productos'] ?? 0);
        $total       = (float)($costos['total'] ?? ($base + $prodCost));
        $costoHa     = (float)($costos['costo_ha'] ?? 0);

        $pagoPorCoop = (bool)($p['pago_por_coop'] ?? false);
        $ctaUrl      = (string)($p['cta_url'] ?? '');
        $coopExtra   = (string)($p['coop_texto_extra'] ?? '');

        $fmtMoney = function (float $n) use ($moneda): string {
            $base = \number_format($n, 2, ',', '.');
            return $moneda ? ($base . ' ' . $moneda) : $base;
        };

        $h = fn(string $s): string => \htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        // Lista de motivos
        $motivosHtml = '';
        if ($motivos) {
            $motivosHtml .= '<ul>';
            foreach ($motivos as $m) {
                $motivosHtml .= '<li>' . $h((string)$m) . '</li>';
            }
            $motivosHtml .= '</ul>';
        } else {
            $motivosHtml = '<em>No informado</em>';
        }

        // Rangos
        $rangosHtml = '';
        if ($rangos) {
            $rangosHtml .= '<ul>';
            foreach ($rangos as $r) {
                $rangosHtml .= '<li>' . $h((string)$r) . '</li>';
            }
            $rangosHtml .= '</ul>';
        } else {
            $rangosHtml = '<em>No informado</em>';
        }

        // Productos
        $productosHtml = '';
        if ($productos) {
            $productosHtml .= '<ul>';
            foreach ($productos as $prod) {
                $productosHtml .= '<li>'
                    . $h((string)($prod['patologia'] ?? ''))
                    . ' — '
                    . $h((string)($prod['fuente'] ?? ''))
                    . ' — '
                    . $h((string)($prod['detalle'] ?? ''))
                    . '</li>';
            }
            $productosHtml .= '</ul>';
        } else {
            $productosHtml = '<em>No se informaron productos.</em>';
        }

        // Dirección
        $dirParts = [];
        if (!empty($direccion['calle'])) {
            $linea = (string)$direccion['calle'];
            if (!empty($direccion['numero'])) {
                $linea .= ' ' . (string)$direccion['numero'];
            }
            $dirParts[] = $linea;
        }
        if (!empty($direccion['localidad'])) {
            $dirParts[] = (string)$direccion['localidad'];
        }
        if (!empty($direccion['provincia'])) {
            $dirParts[] = (string)$direccion['provincia'];
        }
        $dirTexto = $dirParts ? $h(\implode(', ', $dirParts)) : '<em>No informado</em>';

        // Ubicación (coordenadas)
        $ubicTexto = '<em>No informado</em>';
        if (!empty($ubicacion['lat']) && !empty($ubicacion['lng'])) {
            $ubicTexto = $h((string)$ubicacion['lat']) . ', ' . $h((string)$ubicacion['lng']);
        }

        $htmlId = $id > 0 ? '#' . $id : 'N/A';

        // Texto especial para cooperativa (si aplica)
        $coopBlock = '';
        if ($pagoPorCoop && $coopExtra !== '') {
            $coopBlock .= '<h3 style="margin-top:24px;">Información para la cooperativa</h3>';
            $coopBlock .= '<p style="white-space:pre-line;">' . $h($coopExtra) . '</p>';
            if ($ctaUrl !== '') {
                $coopBlock .= '<p style="margin-top:16px;">'
                    . '<a href="' . $h($ctaUrl) . '" '
                    . 'style="display:inline-block;padding:10px 16px;background:#2563eb;color:#ffffff;'
                    . 'text-decoration:none;border-radius:6px;">Ir al panel de gestión</a>'
                    . '</p>';
            }
        }

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Solicitud de servicio de dron ' . $h($htmlId) . '</title>
</head>
<body style="font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; font-size:14px; color:#111827; background-color:#f3f4f6; padding:16px;">
  <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:8px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <h2 style="margin-top:0;margin-bottom:12px;">Solicitud de servicio de pulverización con dron ' . $h($htmlId) . '</h2>

    <p style="margin-top:0;margin-bottom:16px;">
      Se registró una nueva solicitud de servicio de dron en la plataforma SVE.
    </p>

    <h3 style="margin-top:16px;margin-bottom:8px;">Datos del productor</h3>
    <table cellspacing="0" cellpadding="4" style="width:100%;border-collapse:collapse;">
      <tr>
        <td style="width:35%;font-weight:bold;vertical-align:top;">Nombre</td>
        <td>' . $h((string)($productor['nombre'] ?? '')) . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Correo</td>
        <td>' . $h((string)($productor['correo'] ?? '')) . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Cooperativa</td>
        <td>' . $h((string)($cooperativa['nombre'] ?? '')) . '</td>
      </tr>
    </table>

    <h3 style="margin-top:20px;margin-bottom:8px;">Datos del servicio</h3>
    <table cellspacing="0" cellpadding="4" style="width:100%;border-collapse:collapse;">
      <tr>
        <td style="width:35%;font-weight:bold;vertical-align:top;">Superficie (ha)</td>
        <td>' . $h(\number_format($superficie, 2, ',', '.')) . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Forma de pago</td>
        <td>' . $h($formaPago) . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Motivos</td>
        <td>' . $motivosHtml . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Rangos tentativos</td>
        <td>' . $rangosHtml . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Productos</td>
        <td>' . $productosHtml . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Dirección de la finca</td>
        <td>' . $dirTexto . '</td>
      </tr>
      <tr>
        <td style="font-weight:bold;vertical-align:top;">Ubicación (lat, lng)</td>
        <td>' . $ubicTexto . '</td>
      </tr>
    </table>

    <h3 style="margin-top:20px;margin-bottom:8px;">Resumen de costos (estimativo)</h3>
    <table cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;">
      <tr style="background:#f9fafb;">
        <td style="font-weight:bold;">Concepto</td>
        <td style="font-weight:bold;text-align:right;">Monto</td>
      </tr>
      <tr>
        <td>Servicio base (' . $h(\number_format($superficie, 2, ',', '.')) . ' ha × ' . $h($fmtMoney($costoHa)) . ' /ha)</td>
        <td style="text-align:right;">' . $fmtMoney($base) . '</td>
      </tr>
      <tr>
        <td>Productos SVE</td>
        <td style="text-align:right;">' . $fmtMoney($prodCost) . '</td>
      </tr>
      <tr style="background:#f9fafb;">
        <td style="font-weight:bold;">Total estimado</td>
        <td style="font-weight:bold;text-align:right;">' . $fmtMoney($total) . '</td>
      </tr>
    </table>

    ' . $coopBlock . '

    <p style="margin-top:24px;margin-bottom:0;font-size:12px;color:#6b7280;">
      Este correo fue generado automáticamente por la plataforma SVE. Por favor, no responda a este mensaje.
    </p>
  </div>
</body>
</html>';

        return $html;
    }
}
