<?php
// public/index.php

declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

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
    <meta charset="UTF-8">

    <title><?= e(APP_TITLE) ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/assets/style.css">
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
                    ? e($selectedLetter['category'] ?? $selectedLetterKey)
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


                    <?php foreach (($selectedLetter['fields'] ?? []) as $fieldName => $field): ?>
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
