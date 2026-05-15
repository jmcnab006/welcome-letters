<?php

declare(strict_types=1);

function split_string(string $delimiter, string $string, int $element = 0): string
{
    $string = trim($string);

    if ($string === '') {
        return '';
    }

    $parts = explode($delimiter, $string);

    return $parts[$element] ?? '';
}

function get_base_domain(string $domain): string
{
    $domain = trim($domain);
    $parts = explode('.', $domain);

    if (count($parts) < 2) {
        return $domain;
    }

    return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
}

function collect_dns_record(string $domain, string $type, int $timeoutSeconds = 2): string
{
    $domain = trim($domain);
    $type = strtoupper(trim($type));

    if ($domain === '') {
        return '';
    }

    $dnsType = match ($type) {
        'A' => DNS_A,
        'AAAA' => DNS_AAAA,
        'CNAME' => DNS_CNAME,
        'MX' => DNS_MX,
        'TXT' => DNS_TXT,
        'NS' => DNS_NS,
        'SRV' => DNS_SRV,
        default => null,
    };

    if ($dnsType === null) {
        return '';
    }

    $oldTimeout = ini_get('default_socket_timeout');
    ini_set('default_socket_timeout', (string) $timeoutSeconds);

    try {
        $records = @dns_get_record($domain, $dnsType);
    } finally {
        ini_set('default_socket_timeout', (string) $oldTimeout);
    }

    if (!$records) {
        return '';
    }

    $values = [];

    foreach ($records as $record) {
        $value = dns_record_to_string($record);

        if ($value !== '') {
            $values[] = $value;
        }
    }

    return implode(', ', array_unique($values));
}

function dns_record_to_string(array $record): string
{
    return match ($record['type'] ?? '') {
        'A' => $record['ip'] ?? '',
        'AAAA' => $record['ipv6'] ?? '',
        'CNAME', 'NS' => $record['target'] ?? '',
        'TXT' => $record['txt'] ?? '',
        'MX' => trim(($record['pri'] ?? '') . ' ' . ($record['target'] ?? '')),
        'SRV' => trim(
            ($record['pri'] ?? '') . ' ' .
            ($record['weight'] ?? '') . ' ' .
            ($record['port'] ?? '') . ' ' .
            ($record['target'] ?? '')
        ),
        default => '',
    };
}