<?php

return [
    'dns-welcome' => [
        'label' => 'DNS Welcome Letter',
        'category' => 'DNS',
        'description' => 'Send DNS setup information.',
        'template' => 'example.html',

        'subject' => 'DNS Hosting Setup for {{ customer_domain }}',

        'from' => DEFAULT_FROM_EMAIL,
        'from_name' => DEFAULT_FROM_NAME,
        'reply_to' => 'support@example.com',

        'fields' => [
            'to' => [
                'label' => 'To',
                'type' => 'textarea',
                'required' => true,
                'placeholder' => "customer@example.com\nother@example.com",
            ],
            'cc' => [
                'label' => 'CC',
                'type' => 'textarea',
                'required' => false,
            ],
            'bcc' => [
                'label' => 'BCC',
                'type' => 'textarea',
                'required' => false,
            ],
            'customer_domain' => [
                'label' => 'Customer Domain',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'example.com',
            ],
        ],

        'defaults' => [
            'nameserver_1' => 'ns1.example.net',
            'nameserver_2' => 'ns2.example.net',
            'admin_portal_url' => 'https://admin.{{ customer_domain }}',
            'support_email' => 'support@example.com',
            'support_phone' => '952.253.3290',
            'company_name' => 'Example Company',
        ],

        'computed' => [
            'current_mx' => [
                'function' => 'collect_dns_record',
                'args' => [
                    '{{ customer_domain }}',
                    'MX',
                ],
            ],
            'domain_prefix' => [
                'function' => 'split_string',
                'args' => [
                    '.',
                    '{{ customer_domain }}',
                    0,
                ],
            ],
        ],
    ],
];
