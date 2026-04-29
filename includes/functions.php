<?php
// includes/functions.php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function get_letter(array $letters, string $letterKey): ?array
{
    return $letters[$letterKey] ?? null;
}

function render_field(string $fieldName, array $field, mixed $oldValue = null): void
{
    $label = $field['label'] ?? $fieldName;
    $type = $field['type'] ?? 'text';
    $required = !empty($field['required']);
    $placeholder = $field['placeholder'] ?? '';
    $value = $oldValue ?? ($field['default'] ?? '');

    $requiredAttr = $required ? 'required' : '';
    $requiredMark = $required ? ' <span class="required">*</span>' : '';

    echo '<div class="field">';
    echo '<label for="' . e($fieldName) . '">' . e($label) . $requiredMark . '</label>';

    if ($type === 'textarea') {
        echo '<textarea id="' . e($fieldName) . '" name="' . e($fieldName) . '" rows="5" placeholder="' . e($placeholder) . '" ' . $requiredAttr . '>';
        echo e((string) $value);
        echo '</textarea>';
    } elseif ($type === 'select') {
        echo '<select id="' . e($fieldName) . '" name="' . e($fieldName) . '" ' . $requiredAttr . '>';
        echo '<option value="">Select...</option>';

        foreach (($field['options'] ?? []) as $optionValue => $optionLabel) {
            if (is_int($optionValue)) {
                $optionValue = $optionLabel;
            }

            $selected = ((string) $value === (string) $optionValue) ? 'selected' : '';

            echo '<option value="' . e((string) $optionValue) . '" ' . $selected . '>';
            echo e((string) $optionLabel);
            echo '</option>';
        }

        echo '</select>';
    } else {
        $allowedTypes = ['text', 'email', 'number', 'date', 'tel', 'url', 'password'];
        $safeType = in_array($type, $allowedTypes, true) ? $type : 'text';

        echo '<input id="' . e($fieldName) . '"';
        echo ' name="' . e($fieldName) . '"';
        echo ' type="' . e($safeType) . '"';
        echo ' value="' . e((string) $value) . '"';
        echo ' placeholder="' . e($placeholder) . '"';
        echo ' ' . $requiredAttr;
        echo '>';
    }

    if (!empty($field['help'])) {
        echo '<small class="field-help">' . e((string) $field['help']) . '</small>';
    }

    echo '</div>';
}

function validate_letter_submission(array $letter, array $submitted): array
{
    $errors = [];

    foreach (($letter['fields'] ?? []) as $fieldName => $field) {
        $label = $field['label'] ?? $fieldName;
        $type = $field['type'] ?? 'text';
        $required = !empty($field['required']);
        $value = trim((string) ($submitted[$fieldName] ?? ''));

        if ($required && $value === '') {
            $errors[] = "$label is required.";
            continue;
        }

        if ($value === '') {
            continue;
        }

        if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "$label must be a valid email address.";
        }

        if ($type === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = "$label must be a valid URL.";
        }

        if ($type === 'select') {
            $validOptions = [];

            foreach (($field['options'] ?? []) as $optionValue => $optionLabel) {
                $validOptions[] = is_int($optionValue)
                    ? (string) $optionLabel
                    : (string) $optionValue;
            }

            if (!in_array($value, $validOptions, true)) {
                $errors[] = "$label contains an invalid selection.";
            }
        }
    }

    return $errors;
}

function render_subject_template(string $subject, array $values): string
{
    $subject = render_jinja_like($subject, $values, false);
    return trim(preg_replace('/[\r\n]+/', ' ', $subject));
}

function render_letter_template(string $templateName, array $values): string
{
    $safeTemplate = basename($templateName);
    $templateFile = TEMPLATE_PATH . '/' . $safeTemplate;

    if (!is_file($templateFile)) {
        throw new RuntimeException("Template not found: {$safeTemplate}");
    }

    $template = file_get_contents($templateFile);

    if ($template === false) {
        throw new RuntimeException("Unable to read template: {$safeTemplate}");
    }

    return render_jinja_like($template, $values, true);
}

function render_jinja_like(string $template, array $values, bool $escapeHtml = true): string
{
    /*
     * Supports:
     *   {{ variable }}
     *   {% if variable %}...{% endif %}
     *   {% if variable %}...{% else %}...{% endif %}
     *
     * This is intentionally small and safe.
     * It is not full Jinja/Twig.
     */

    $previous = null;

    while ($previous !== $template) {
        $previous = $template;

        $template = preg_replace_callback(
            '/{%\s*if\s+([a-zA-Z0-9_\-]+)\s*%}(.*?)(?:{%\s*else\s*%}(.*?))?{%\s*endif\s*%}/s',
            function (array $matches) use ($values, $escapeHtml): string {
                $key = $matches[1];
                $ifBlock = $matches[2];
                $elseBlock = $matches[3] ?? '';

                $value = $values[$key] ?? null;

                return !empty($value)
                    ? render_jinja_like($ifBlock, $values, $escapeHtml)
                    : render_jinja_like($elseBlock, $values, $escapeHtml);
            },
            $template
        );
    }

    return preg_replace_callback(
        '/{{\s*([a-zA-Z0-9_\-]+)\s*}}/',
        function (array $matches) use ($values, $escapeHtml): string {
            $key = $matches[1];
            $value = (string) ($values[$key] ?? '');

            return $escapeHtml ? nl2br(e($value)) : $value;
        },
        $template
    );
}

function html_to_plain_text(string $html): string
{
    $text = $html;

    $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $text);
    $text = preg_replace('/<\/p\s*>/i', "\n\n", $text);
    $text = preg_replace('/<\/div\s*>/i', "\n", $text);
    $text = preg_replace('/<\/h[1-6]\s*>/i', "\n\n", $text);
    $text = preg_replace('/<\/li\s*>/i', "\n", $text);
    $text = preg_replace('/<li[^>]*>/i', '- ', $text);

    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $text = preg_replace("/[ \t]+/", ' ', $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);

    return trim($text);
}
function apply_autofill_values(array $letter, array $submitted): array
{
    foreach (($letter['fields'] ?? []) as $fieldName => $field) {
        if (empty($field['autofill']) || !is_array($field['autofill'])) {
            continue;
        }

        $selected = $submitted[$fieldName] ?? '';

        if ($selected !== '' && isset($field['autofill'][$selected])) {
            foreach ($field['autofill'][$selected] as $targetField => $targetValue) {
                if (($submitted[$targetField] ?? '') === '') {
                    $submitted[$targetField] = $targetValue;
                }
            }
        }
    }

    return $submitted;
}
