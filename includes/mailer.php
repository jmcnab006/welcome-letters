<?php
require_once __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send a welcome letter email
 *
 * @param array|string $to
 * @param array $cc
 * @param array $bcc
 * @param string $subject
 * @param string $htmlBody
 * @param string $plainBody
 * @param string $from
 * @param string $fromName
 * @param string|null $replyTo
 * @return bool
 */
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
    $mail = new PHPMailer(true);

    try {
        /**
         * ---------------------------------------------------------------------
         * SMTP CONFIG (EDIT THESE)
         * ---------------------------------------------------------------------
         */
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->Username   = 'smtp-user@example.com';
        $mail->Password   = 'your-password';

        // Encryption: tls (587) or ssl (465)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Enable UTF-8 / internationalized email (RFC 6531/6532)
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        /**
         * ---------------------------------------------------------------------
         * HEADERS / ENVELOPE
         * ---------------------------------------------------------------------
         */
        $mail->setFrom($from, $fromName);

        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }

        // Normalize recipients
        $toList  = normalize_email_array($to);
        $ccList  = normalize_email_array($cc);
        $bccList = normalize_email_array($bcc);

        foreach ($toList as $addr) {
            $mail->addAddress($addr);
        }

        foreach ($ccList as $addr) {
            $mail->addCC($addr);
        }

        foreach ($bccList as $addr) {
            $mail->addBCC($addr);
        }

        /**
         * ---------------------------------------------------------------------
         * MESSAGE BODY (RFC 2045 multipart/alternative)
         * ---------------------------------------------------------------------
         */
        $mail->isHTML(true);
        $mail->Subject = sanitize_header($subject);

        $mail->Body    = build_html_document($htmlBody);
        $mail->AltBody = $plainBody;

        /**
         * ---------------------------------------------------------------------
         * OPTIONAL: DEBUG LOGGING
         * ---------------------------------------------------------------------
         */
        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
            $mail->SMTPDebug = 0;

            file_put_contents(
                __DIR__ . '/../mail-debug.log',
                "---- " . date('c') . " ----\n" .
                "To: " . implode(',', $toList) . "\n" .
                "CC: " . implode(',', $ccList) . "\n" .
                "BCC: " . implode(',', $bccList) . "\n" .
                "Subject: $subject\n\n",
                FILE_APPEND
            );
        }

        /**
         * ---------------------------------------------------------------------
         * SEND
         * ---------------------------------------------------------------------
         */
        return $mail->send();

    } catch (Exception $e) {
        // Log error for debugging
        file_put_contents(
            __DIR__ . '/../mail-error.log',
            "---- " . date('c') . " ----\n" .
            $e->getMessage() . "\n\n",
            FILE_APPEND
        );

        return false;
    }
}

/**
 * Normalize email input to array of valid emails
 */
function normalize_email_array(array|string|null $input): array
{
    if ($input === null) return [];

    if (is_string($input)) {
        $input = explode(',', $input);
    }

    $valid = [];

    foreach ($input as $email) {
        $email = trim((string)$email);

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid[] = $email;
        }
    }

    return array_values(array_unique($valid));
}

/**
 * Prevent header injection (RFC 5322 safety)
 */
function sanitize_header(string $value): string
{
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}

/**
 * Wrap HTML in a full document (better compatibility)
 */
function build_html_document(string $body): string
{
    return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
$body
</body>
</html>
HTML;
}
