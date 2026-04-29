<?php
// public/send.php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

function fail_and_redirect(string $message, string $letterKey, array $oldInput = []): never
{
    $_SESSION['last_error'] = $message;
    $_SESSION['old_input'] = $oldInput;

    $location = 'index.php?status=error';

    if ($letterKey !== '') {
        $location .= '&letter=' . urlencode($letterKey);
    }

    header('Location: ' . $location);
    exit;
}

function success_redirect(string $letterKey): never
{
    $location = 'index.php?status=sent';

    if ($letterKey !== '') {
        $location .= '&letter=' . urlencode($letterKey);
    }

    header('Location: ' . $location);
    exit;
}

function preview_redirect(string $letterKey): never
{
    header('Location: index.php?letter=' . urlencode($letterKey) . '&preview=1');
    exit;
}

function normalize_email_list(array|string|null $emails): array
{
    if ($emails === null) {
        return [];
    }

    if (is_string($emails)) {
        $emails = array_filter(array_map('trim', explode(',', $emails)));
    }

    $valid = [];

    foreach ($emails as $email) {
        $email = trim((string) $email);

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid[] = $email;
        }
    }

    return array_values(array_unique($valid));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$letterKey = (string) ($_POST['_letter'] ?? '');
$csrf = (string) ($_POST['_csrf'] ?? '');
$action = (string) ($_POST['_action'] ?? 'send');

if (!in_array($action, ['preview', 'send'], true)) {
    $action = 'send';
}

if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    fail_and_redirect('Security token expired. Reload the page and try again.', $letterKey);
}

$letter = get_letter($letters, $letterKey);

if (!$letter) {
    fail_and_redirect('Unknown welcome letter.', $letterKey);
}

$submitted = [
    'customer_email' => trim((string) ($_POST['customer_email'] ?? '')),
];

foreach (($letter['fields'] ?? []) as $fieldName => $field) {
    $value = $_POST[$fieldName] ?? '';
    $submitted[$fieldName] = is_array($value) ? '' : trim((string) $value);
}

$submitted = apply_autofill_values($letter, $submitted);

$errors = validate_letter_submission($letter, $submitted);

if (!filter_var($submitted['customer_email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Customer Email must be a valid email address.';
}

if ($errors) {
    fail_and_redirect(implode(' ', $errors), $letterKey, $submitted);
}

$templateValues = array_merge(
    $letter['defaults'] ?? [],
    $submitted,
    [
        'sent_date' => date('Y-m-d'),
        'sent_datetime' => date('Y-m-d H:i:s'),
    ]
);

try {
    $subject = render_subject_template($letter['subject'] ?? 'Welcome', $templateValues);
    $htmlBody = render_letter_template($letter['template'], $templateValues);
    $plainBody = html_to_plain_text($htmlBody);
} catch (Throwable $e) {
    fail_and_redirect(
        'Template rendering failed: ' . $e->getMessage(),
        $letterKey,
        $submitted
    );
}

$to = normalize_email_list($letter['to'] ?? $submitted['customer_email']);
$cc = normalize_email_list($letter['cc'] ?? []);
$bcc = normalize_email_list($letter['bcc'] ?? []);

if (!$to) {
    $to = [$submitted['customer_email']];
}

if ($action === 'preview') {
    $_SESSION['preview'] = [
        'letter' => $letterKey,
        'to' => $to,
        'cc' => $cc,
        'bcc' => $bcc,
        'from' => $letter['from'] ?? DEFAULT_FROM_EMAIL,
        'from_name' => $letter['from_name'] ?? DEFAULT_FROM_NAME,
        'reply_to' => $letter['reply_to'] ?? null,
        'subject' => $subject,
        'html' => $htmlBody,
        'plain' => $plainBody,
        'input' => $submitted,
    ];

    $_SESSION['old_input'] = $submitted;

    preview_redirect($letterKey);
}

$sent = send_welcome_letter(
    to: $to,
    cc: $cc,
    bcc: $bcc,
    subject: $subject,
    htmlBody: $htmlBody,
    plainBody: $plainBody,
    from: $letter['from'] ?? DEFAULT_FROM_EMAIL,
    fromName: $letter['from_name'] ?? DEFAULT_FROM_NAME,
    replyTo: $letter['reply_to'] ?? null
);

if (!$sent) {
    fail_and_redirect(
        'The email could not be sent. Check your SMTP settings or mail logs.',
        $letterKey,
        $submitted
    );
}

$_SESSION['csrf'] = bin2hex(random_bytes(32));

unset(
    $_SESSION['last_error'],
    $_SESSION['old_input'],
    $_SESSION['preview']
);

success_redirect($letterKey);
