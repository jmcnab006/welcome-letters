<?php
// includes/mailer.php

declare(strict_types=1);

require_once __DIR__ . '/../vendors/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendors/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendors/phpmailer/Exception.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

openlog('welcome-letters', LOG_PID | LOG_PERROR, LOG_MAIL);

function send_welcome_letter(
    array|string $to,
    array $cc,
    array $bcc,
    string $subject,
    string $htmlBody,
    string $plainBody,
    string $from,
    string $fromName,
    ?string $replyTo = null
): bool {

    $requestId = bin2hex(random_bytes(6));
    $mail = new PHPMailer(true);

    try {
        $toList = normalize_email_array($to);
        $ccList = normalize_email_array($cc);
        $bccList = normalize_email_array($bcc);

        if (!$toList) {
            syslog(LOG_ERR, "[req:$requestId] No valid recipients.");
            return false;
        }

        // SMTP CONFIG
        $mail->isSMTP();
        $mail->Host = SMTP_HOST ?: 'localhost';
        $mail->Port = (int)SMTP_PORT ?: 25;
        $mail->SMTPAuth = filter_var(SMTP_AUTH ?: 'false', FILTER_VALIDATE_BOOL);

        if ($mail->SMTPAuth) {
            $mail->Username = SMTP_USER ?: '';
            $mail->Password = SMTP_PASS ?: '';
        }

        $secure = strtolower(SMTP_SECURE ?: '');

        if ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        // DEBUG LOGGING
        if (filter_var(SMTP_DEBUG?: 'false', FILTER_VALIDATE_BOOL)) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function ($str, $level) use ($requestId) {
                syslog(LOG_DEBUG, "[req:$requestId][SMTP:$level] " . trim($str));
            };
        }

        // EMAIL SETTINGS
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);

        $mail->setFrom($from, $fromName);

        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }

        foreach ($toList as $addr) {
            $mail->addAddress($addr);
        }

        foreach ($ccList as $addr) {
            $mail->addCC($addr);
        }

        foreach ($bccList as $addr) {
            $mail->addBCC($addr);
        }

        $mail->Subject = sanitize_mail_header($subject);
        $mail->Body = wrap_html_email($htmlBody);
        $mail->AltBody = $plainBody;

        syslog(LOG_INFO, json_encode([
            'event' => 'email_send_attempt',
            'req' => $requestId,
            'to' => $toList,
            'subject' => $mail->Subject,
        ]));

        $mail->send();

        syslog(LOG_INFO, json_encode([
            'event' => 'email_send_success',
            'req' => $requestId,
            'to' => $toList,
        ]));

        return true;

    } catch (Exception $e) {
        syslog(LOG_ERR, "[req:$requestId] Mail error: " . $mail->ErrorInfo);
        return false;
    } catch (Throwable $e) {
        syslog(LOG_ERR, "[req:$requestId] Exception: " . $e->getMessage());
        return false;
    }
}

/*
 * Helpers
 */

function normalize_email_array(array|string|null $input): array
{
    if ($input === null) return [];

    if (is_string($input)) {
        $input = explode(',', $input);
    }

    $out = [];

    foreach ($input as $email) {
        $email = trim((string)$email);
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $out[] = $email;
        }
    }

    return array_values(array_unique($out));
}

function sanitize_mail_header(string $value): string
{
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}

function wrap_html_email(string $html): string
{
    if (stripos($html, '<html') !== false) {
        return $html;
    }

    return "<!doctype html>
<html>
<head><meta charset=\"UTF-8\"></head>
<body>
$html
</body>
</html>";
}