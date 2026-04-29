<?php
// config/letters.php

return [
    'dns-welcome' => [
        'label' => 'DNS Welcome Letter',
        'category' => 'DNS',
        'description' => 'Send DNS nameserver instructions and optional portal access.',
        'template' => 'dns-welcome.html',

        'subject' => 'Welcome to DNS Hosting for {{ domain }}',

        'from' => 'noreply@example.com',
        'from_name' => 'Example Company',
        'reply_to' => 'support@example.com',

        'to' => '{{ customer_email }}',
        'cc' => [],
        'bcc' => ['archive@example.com'],

        'fields' => [
            'customer_name' => [
                'label' => 'Customer Name',
                'type' => 'text',
                'required' => true,
            ],

            'customer_email' => [
                'label' => 'Customer Email',
                'type' => 'email',
                'required' => true,
            ],

            'company_name' => [
                'label' => 'Company Name',
                'type' => 'text',
                'required' => false,
                'default' => 'Example Company',
            ],

            'domain' => [
                'label' => 'Domain',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'example.com',
            ],

            'dns_cluster' => [
                'label' => 'DNS Cluster',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'standard' => 'Standard DNS Cluster',
                    'premium' => 'Premium DNS Cluster',
                    'legacy' => 'Legacy DNS Cluster',
                ],
                'autofill' => [
                    'standard' => [
                        'nameservers' => [
                            'ns1.example.net',
                            'ns2.example.net',
                        ],
                        'nameserver_1' => 'ns1.example.net',
                        'nameserver_2' => 'ns2.example.net',
                    ],
                    'premium' => [
                        'nameservers' => [
                            'ns1.premium.example.net',
                            'ns2.premium.example.net',
                            'ns3.premium.example.net',
                        ],
                        'nameserver_1' => 'ns1.premium.example.net',
                        'nameserver_2' => 'ns2.premium.example.net',
                    ],
                    'legacy' => [
                        'nameservers' => [
                            'dns1.legacy.example.net',
                            'dns2.legacy.example.net',
                        ],
                        'nameserver_1' => 'dns1.legacy.example.net',
                        'nameserver_2' => 'dns2.legacy.example.net',
                    ],
                ],
            ],

            'admin_portal_url' => [
                'label' => 'Admin Portal URL',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://portal.example.com',
            ],

            'admin_username' => [
                'label' => 'Admin Username',
                'type' => 'text',
                'required' => false,
            ],

            'admin_password' => [
                'label' => 'Admin Password',
                'type' => 'text',
                'required' => false,
            ],

            'notes' => [
                'label' => 'Internal Notes',
                'type' => 'textarea',
                'required' => false,
                'help' => 'Optional. Included only if your template references {{ notes }}.',
            ],
        ],

        'defaults' => [
            'nameservers' => [
                'ns1.example.net',
                'ns2.example.net',
            ],
            'nameserver_1' => 'ns1.example.net',
            'nameserver_2' => 'ns2.example.net',
            'support_email' => 'support@example.com',
            'support_phone' => '555-555-5555',
            'company_name' => 'Example Company',
        ],
    ],

    'webhosting-login' => [
        'label' => 'Web Hosting Login',
        'category' => 'Web Hosting',
        'description' => 'Send control panel, FTP/SFTP, and server information.',
        'template' => 'webhosting-login.html',

        'subject' => 'Your Web Hosting Account for {{ domain }}',

        'from' => 'noreply@example.com',
        'from_name' => 'Example Company',
        'reply_to' => 'support@example.com',

        'to' => '{{ customer_email }}',
        'cc' => ['hosting@example.com'],
        'bcc' => ['archive@example.com'],

        'fields' => [
            'customer_name' => [
                'label' => 'Customer Name',
                'type' => 'text',
                'required' => true,
            ],

            'customer_email' => [
                'label' => 'Customer Email',
                'type' => 'email',
                'required' => true,
            ],

            'domain' => [
                'label' => 'Domain',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'example.com',
            ],

            'web_server' => [
                'label' => 'Web Server',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'web01' => 'web01 - Standard Linux Hosting',
                    'web02' => 'web02 - High Capacity Linux Hosting',
                    'win01' => 'win01 - Windows Hosting',
                ],
                'autofill' => [
                    'web01' => [
                        'server_hostname' => 'web01.example.net',
                        'control_panel_url' => 'https://cpanel01.example.net',
                        'ftp_host' => 'web01.example.net',
                        'ssh_host' => 'web01.example.net',
                        'php_version' => '8.3',
                    ],
                    'web02' => [
                        'server_hostname' => 'web02.example.net',
                        'control_panel_url' => 'https://cpanel02.example.net',
                        'ftp_host' => 'web02.example.net',
                        'ssh_host' => 'web02.example.net',
                        'php_version' => '8.3',
                    ],
                    'win01' => [
                        'server_hostname' => 'win01.example.net',
                        'control_panel_url' => 'https://panel-win01.example.net',
                        'ftp_host' => 'win01.example.net',
                        'ssh_host' => '',
                        'php_version' => '',
                    ],
                ],
            ],

            'username' => [
                'label' => 'Username',
                'type' => 'text',
                'required' => true,
            ],

            'password' => [
                'label' => 'Password',
                'type' => 'text',
                'required' => true,
            ],

            'control_panel_url' => [
                'label' => 'Control Panel URL',
                'type' => 'url',
                'required' => false,
            ],

            'ftp_host' => [
                'label' => 'FTP/SFTP Host',
                'type' => 'text',
                'required' => false,
            ],

            'ssh_host' => [
                'label' => 'SSH Host',
                'type' => 'text',
                'required' => false,
            ],

            'server_hostname' => [
                'label' => 'Server Hostname',
                'type' => 'text',
                'required' => false,
            ],

            'php_version' => [
                'label' => 'PHP Version',
                'type' => 'text',
                'required' => false,
            ],
        ],

        'defaults' => [
            'support_email' => 'support@example.com',
            'support_phone' => '555-555-5555',
            'company_name' => 'Example Company',
        ],
    ],

    'mail-service-welcome' => [
        'label' => 'Mail Service Welcome',
        'category' => 'Mail',
        'description' => 'Send mailbox setup details and mail server settings.',
        'template' => 'mail-service-welcome.html',

        'subject' => 'Your Email Service Setup Information',

        'from' => 'noreply@example.com',
        'from_name' => 'Example Company',
        'reply_to' => 'support@example.com',

        'to' => '{{ customer_email }}',
        'cc' => ['mail-admin@example.com'],
        'bcc' => ['archive@example.com'],

        'fields' => [
            'customer_name' => [
                'label' => 'Customer Name',
                'type' => 'text',
                'required' => true,
            ],

            'customer_email' => [
                'label' => 'Customer Email',
                'type' => 'email',
                'required' => true,
            ],

            'email_address' => [
                'label' => 'Mailbox Address',
                'type' => 'email',
                'required' => true,
            ],

            'temporary_password' => [
                'label' => 'Temporary Password',
                'type' => 'text',
                'required' => true,
            ],

            'mail_cluster' => [
                'label' => 'Mail Cluster',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'mail01' => 'mail01 - Standard Mail',
                    'mail02' => 'mail02 - Business Mail',
                ],
                'autofill' => [
                    'mail01' => [
                        'imap_server' => 'mail01.example.net',
                        'smtp_server' => 'mail01.example.net',
                        'webmail_url' => 'https://webmail01.example.net',
                    ],
                    'mail02' => [
                        'imap_server' => 'mail02.example.net',
                        'smtp_server' => 'mail02.example.net',
                        'webmail_url' => 'https://webmail02.example.net',
                    ],
                ],
            ],

            'imap_server' => [
                'label' => 'IMAP Server',
                'type' => 'text',
                'required' => false,
            ],

            'smtp_server' => [
                'label' => 'SMTP Server',
                'type' => 'text',
                'required' => false,
            ],

            'webmail_url' => [
                'label' => 'Webmail URL',
                'type' => 'url',
                'required' => false,
            ],
        ],

        'defaults' => [
            'imap_port' => '993',
            'imap_security' => 'SSL/TLS',
            'smtp_port' => '587',
            'smtp_security' => 'STARTTLS',
            'support_email' => 'support@example.com',
            'support_phone' => '555-555-5555',
            'company_name' => 'Example Company',
        ],
    ],
];
