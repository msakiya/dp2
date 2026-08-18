<?php
/**
 * RC Depósitos — Form Handler con PHPMailer
 * Envía correos de contacto desde el formulario
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Prevenir que errores de PHP rompan el JSON
ini_set('display_errors', 0);
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo json_encode(['success' => false, 'message' => "PHP FATAL ERROR: {$err['message']} in {$err['file']} on line {$err['line']}"]);
    }
});

// Cargar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

// ============================================
// CONFIGURACIÓN — AJUSTA ESTOS VALORES
// ============================================

// Correo destino (donde recibirás los mensajes)
$DESTINATION_EMAIL = 'moises@republicacocinera.com';
$DESTINATION_NAME = 'Moises';

// Configuración SMTP
$USE_SMTP = true;
$SMTP_HOST = 'smtp.stackmail.com';
$SMTP_PORT = 587;
$SMTP_USER = 'juan@topytop.com';
$SMTP_PASS = 'Mw197f32e';
$SMTP_SECURE = PHPMailer::ENCRYPTION_STARTTLS;

// ============================================
// VALIDACIÓN DE DATOS
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validaciones
if (empty($nombre) || empty($telefono) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
    exit;
}

if (!preg_match('/^[0-9\s]{9,15}$/', $telefono)) {
    echo json_encode(['success' => false, 'message' => 'El número de teléfono no es válido.']);
    exit;
}

// Sanitizar
$nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$telefono = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

// ============================================
// CONSTRUIR EL EMAIL
// ============================================

$subject = "Nuevo contacto desde RC Depósitos — $nombre";

$htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: #0B0F1A; padding: 30px; text-align: center; }
        .header h1 { color: #F5C518; margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .field { margin-bottom: 20px; }
        .field-label { font-weight: bold; color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .field-value { color: #555; font-size: 16px; padding: 10px; background: #f8f9fa; border-radius: 6px; }
        .footer { padding: 20px 30px; background: #f8f9fa; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 RC Depósitos</h1>
            <p style="color: #fff; margin: 10px 0 0; font-size: 14px;">Nuevo contacto desde la web</p>
        </div>
        <div class="content">
            <div class="field">
                <div class="field-label">Nombre completo</div>
                <div class="field-value">{$nombre}</div>
            </div>
            <div class="field">
                <div class="field-label">Teléfono</div>
                <div class="field-value">{$telefono}</div>
            </div>
            <div class="field">
                <div class="field-label">Correo electrónico</div>
                <div class="field-value">{$email}</div>
            </div>
        </div>
        <div class="footer">
            Enviado desde rc-depositos.com | Fecha: " . date('d/m/Y H:i') . "
        </div>
    </div>
</body>
</html>
HTML;

$textBody = "Nuevo contacto desde RC Depósitos\n\n";
$textBody .= "Nombre: {$nombre}\n";
$textBody .= "Teléfono: {$telefono}\n";
$textBody .= "Email: {$email}\n";
$textBody .= "Fecha: " . date('d/m/Y H:i') . "\n";

// ============================================
// ENVIAR CON PHPMailer
// ============================================

try {
    $mail = new PHPMailer(true);

    $debugOutput = '';

    if ($USE_SMTP) {
        $mail->isSMTP();
        $mail->Host = $SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $SMTP_USER;
        $mail->Password = $SMTP_PASS;
        $mail->SMTPSecure = $SMTP_SECURE;
        $mail->Port = $SMTP_PORT;
        
        $mail->SMTPDebug = 2; 
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= "[$level] $str\n";
        };
        $mail->Timeout = 10;
    } else {
        $mail->isMail();
    }

    $mail->CharSet = 'UTF-8';
    if ($USE_SMTP) {
        $mail->setFrom($SMTP_USER, 'RC Depósitos Web');
    } else {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'republicacocinera.com';
        $host = str_replace('www.', '', $host);
        $mail->setFrom('no-reply@' . $host, 'RC Depósitos Web');
    }
    $mail->addAddress($DESTINATION_EMAIL, $DESTINATION_NAME);
    
    // Aquí puedes reemplazar los correos de ejemplo con los verdaderos cuando los tengas:
    $mail->addAddress('correo2@ejemplo.com', 'Destinatario 2');
    $mail->addAddress('correo3@ejemplo.com', 'Destinatario 3');

    $mail->addReplyTo($email, $nombre);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Mensaje enviado correctamente.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "ERROR PHPMailer: " . $e->getMessage() . "\n\nDEBUG LOG:\n" . $debugOutput
    ]);
}
