<?php
// public/index.php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$selectedLetterKey = $_GET['letter'] ?? '';
$selectedLetter = $selectedLetterKey ? get_letter($letters, $selectedLetterKey) : null;

$status = $_GET['status'] ?? '';
$errorMessage = $_SESSION['last_error'] ?? '';
$oldInput = $_SESSION['old_input'] ?? [];
$preview = $_SESSION['preview'] ?? null;

unset($_SESSION['last_error']);

$lettersByCategory = [];

foreach ($letters as $key => $letter) {
    $category = $letter['category'] ?? 'General';
    $lettersByCategory[$category][$key] = $letter;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= e(APP_TITLE) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f4f6f8;
            color: #1f2937;
        }

        .app {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 340px 1fr;
        }

        .sidebar {
            background: #111827;
            color: #fff;
            padding: 24px;
            overflow-y: auto;
        }

        .sidebar h1 {
            font-size: 22px;
            margin: 0 0 6px;
        }

        .sidebar .subtitle {
            margin: 0 0 28px;
            color: #9ca3af;
            font-size: 14px;
        }

        .category {
            margin-bottom: 26px;
        }

        .category h2 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9ca3af;
            margin: 0 0 10px;
        }

        .letter-list {
            display: grid;
            gap: 8px;
        }

        .letter-link {
            display: block;
            padding: 12px 14px;
            border-radius: 12px;
            text-decoration: none;
            color: #e5e7eb;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .letter-link:hover {
            background: rgba(255, 255, 255, .09);
        }

        .letter-link.active {
            background: #2563eb;
            border-color: #60a5fa;
            color: #fff;
        }

        .letter-link strong {
            display: block;
            font-size: 15px;
            margin-bottom: 3px;
        }

        .letter-link span {
            display: block;
            font-size: 13px;
            color: #cbd5e1;
            line-height: 1.35;
        }

        .content {
            padding: 32px;
            overflow-y: auto;
        }

        .topbar {
            margin-bottom: 22px;
        }

        .topbar h2 {
            margin: 0 0 4px;
            font-size: 28px;
        }

        .topbar p {
            margin: 0;
            color: #6b7280;
        }

        .notice {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .notice.success {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .notice.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .panel {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .panel-header {
            padding: 24px 28px;
            border-bottom: 1px solid #e5e7eb;
            background: #fbfdff;
        }

        .panel-header h3 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .panel-header p {
            margin: 0;
            color: #6b7280;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .meta-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
        }

        .meta-card dt {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .meta-card dd {
            margin: 0;
            font-size: 14px;
            word-break: break-word;
        }

        .letter-form {
            padding: 28px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            display: grid;
            gap: 7px;
        }

        .field-full,
        .field:has(textarea) {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 700;
            font-size: 14px;
        }

        .required {
            color: #dc2626;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 11px;
            padding: 11px 12px;
            font: inherit;
            background: #fff;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .field-help {
            color: #6b7280;
            font-size: 12px;
        }

        .defaults-box {
            grid-column: 1 / -1;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
        }

        .defaults-box summary {
            cursor: pointer;
            font-weight: 700;
        }

        .defaults-box table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
            font-size: 14px;
        }

        .defaults-box th,
        .defaults-box td {
            padding: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        .actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        button {
            border: 0;
            border-radius: 11px;
            padding: 12px 18px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        .btn-preview {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-send {
            background: #2563eb;
            color: #fff;
        }

        .empty-state {
            padding: 70px 30px;
            text-align: center;
        }

        .empty-state h3 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        .empty-state p {
            margin: 0;
            color: #6b7280;
        }

        .preview-panel {
            margin-top: 24px;
        }

        .preview-meta {
            padding: 18px 22px;
            border-bottom: 1px solid #e5e7eb;
            background: #fbfdff;
            font-size: 14px;
        }

        .preview-meta p {
            margin: 5px 0;
        }

        .email-preview {
            width: 100%;
            height: 520px;
            border: 0;
            background: #fff;
        }

        @media (max-width: 900px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                max-height: none;
            }

            .letter-form,
            .meta {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
<div class="app">

    <aside class="sidebar">
        <h1><?= e(APP_TITLE) ?></h1>
        <p class="subtitle">Welcome letters and customer setup emails</p>

        <?php foreach ($lettersByCategory as $category => $categoryLetters): ?>
            <section class="category">
                <h2><?= e($category) ?></h2>

                <div class="letter-list">
                    <?php foreach ($categoryLetters as $key => $letter): ?>
                        <a class="letter-link <?= $key === $selectedLetterKey ? 'active' : '' ?>"
                           href="?letter=<?= urlencode($key) ?>">
                            <strong><?= e($letter['label'] ?? $key) ?></strong>

                            <?php if (!empty($letter['description'])): ?>
                                <span><?= e($letter['description']) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </aside>

    <main class="content">

        <div class="topbar">
            <h2>
                <?= $selectedLetter
                    ? e($selectedLetter['label'] ?? $selectedLetterKey)
                    : 'Select a Letter'
                ?>
            </h2>
            <p>
                <?= $selectedLetter
                    ? e($selectedLetter['description'] ?? 'Fill out the form below.')
                    : 'Choose a letter from the list on the left.'
                ?>
            </p>
        </div>

        <?php if ($status === 'sent'): ?>
            <div class="notice success">Welcome letter sent successfully.</div>
        <?php elseif ($status === 'error'): ?>
            <div class="notice error">
                <?= e($errorMessage ?: 'The welcome letter could not be sent.') ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <?php if (!$selectedLetter): ?>

                <div class="empty-state">
                    <h3>No letter selected</h3>
                    <p>Select a category and letter from the left panel.</p>
                </div>

            <?php else: ?>

                <div class="panel-header">
                    <h3><?= e($selectedLetter['label'] ?? $selectedLetterKey) ?></h3>
                    <p><?= e($selectedLetter['description'] ?? '') ?></p>

                    <dl class="meta">
                        <div class="meta-card">
                            <dt>From</dt>
                            <dd><?= e($selectedLetter['from'] ?? DEFAULT_FROM_EMAIL) ?></dd>
                        </div>

                        <div class="meta-card">
                            <dt>Subject</dt>
                            <dd><?= e($selectedLetter['subject'] ?? '') ?></dd>
                        </div>

                        <div class="meta-card">
                            <dt>Template</dt>
                            <dd><?= e($selectedLetter['template'] ?? '') ?></dd>
                        </div>
                    </dl>
                </div>

                <form method="post" action="send.php" class="letter-form" autocomplete="off">
                    <input type="hidden" name="_csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <input type="hidden" name="_letter" value="<?= e($selectedLetterKey) ?>">

                    <div class="field field-full">
                        <label for="customer_email">
                            Customer Email <span class="required">*</span>
                        </label>
                        <input id="customer_email"
                               name="customer_email"
                               type="email"
                               required
                               value="<?= e($oldInput['customer_email'] ?? '') ?>"
                               placeholder="customer@example.com">
                    </div>

                    <?php foreach (($selectedLetter['fields'] ?? []) as $fieldName => $field): ?>
                        <?php if ($fieldName === 'customer_email') continue; ?>
                        <?php render_field($fieldName, $field, $oldInput[$fieldName] ?? null); ?>
                    <?php endforeach; ?>

                    <?php if (!empty($selectedLetter['defaults'])): ?>
                        <details class="defaults-box">
                            <summary>Defaults included in this letter</summary>

                            <table>
                                <tbody>
                                <?php foreach ($selectedLetter['defaults'] as $name => $value): ?>
                                    <tr>
                                        <th><?= e((string) $name) ?></th>
                                        <td>
                                            <?php if (is_array($value)): ?>
                                                <?= e(implode(', ', array_map('strval', $value))) ?>
                                            <?php else: ?>
                                                <?= e((string) $value) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </details>
                    <?php endif; ?>

                    <div class="actions">
                        <button class="btn-preview" type="submit" name="_action" value="preview">
                            Preview
                        </button>

                        <button class="btn-send" type="submit" name="_action" value="send">
                            Send Welcome Letter
                        </button>
                    </div>
                </form>

            <?php endif; ?>
        </section>

        <?php if (!empty($_GET['preview']) && $preview && ($preview['letter'] ?? '') === $selectedLetterKey): ?>
            <section class="panel preview-panel">
                <div class="panel-header">
                    <h3>Email Preview</h3>
                    <p>Review the rendered message before sending.</p>
                </div>

                <div class="preview-meta">
                    <p><strong>To:</strong> <?= e(implode(', ', $preview['to'] ?? [])) ?></p>

                    <?php if (!empty($preview['cc'])): ?>
                        <p><strong>CC:</strong> <?= e(implode(', ', $preview['cc'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($preview['bcc'])): ?>
                        <p><strong>BCC:</strong> <?= e(implode(', ', $preview['bcc'])) ?></p>
                    <?php endif; ?>

                    <p><strong>Subject:</strong> <?= e($preview['subject'] ?? '') ?></p>
                </div>

                <iframe class="email-preview"
                        sandbox=""
                        srcdoc="<?= e($preview['html'] ?? '') ?>"></iframe>
            </section>
        <?php endif; ?>

    </main>
</div>

<script>
document.querySelectorAll('select[data-autofill]').forEach((select) => {
    select.addEventListener('change', () => {
        let map = {};

        try {
            map = JSON.parse(select.dataset.autofill || '{}');
        } catch (e) {
            return;
        }

        const selected = select.value;

        if (!map[selected]) {
            return;
        }

        Object.entries(map[selected]).forEach(([fieldName, value]) => {
            const field = document.querySelector(`[name="${CSS.escape(fieldName)}"]`);

            if (!field) {
                return;
            }

            if (Array.isArray(value)) {
                field.value = value.join(', ');
            } else {
                field.value = value;
            }
        });
    });
});
</script>

</body>
</html>
