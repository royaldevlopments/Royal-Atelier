<?php

return [
    'version' => '1.0.0',
    'name' => 'Royal Atelier',
    'settings' => [
        'show_in_sidebar' => env('RX_SHOW_IN_SIDEBAR', true),
    ],

    'flags' => [
        'show_in_sidebar' => [
            'label' => 'Show in Sidebar',
            'description' => 'Display the Extensions link in the admin sidebar.',
            'type' => 'boolean',
            'default' => true,
        ],
        'disable_attribution' => [
            'label' => 'Disable Attribution',
            'description' => 'Remove "Powered by" branding from the panel footer.',
            'type' => 'boolean',
            'default' => false,
        ],
        'remote_metadata' => [
            'label' => 'Remote Metadata',
            'description' => 'Fetch extension metadata from remote sources.',
            'type' => 'boolean',
            'default' => false,
        ],
        'dev_mode' => [
            'label' => 'Developer Mode',
            'description' => 'Enable development features and verbose logging.',
            'type' => 'boolean',
            'default' => false,
        ],
        'auto_build' => [
            'label' => 'Auto Build',
            'description' => 'Automatically rebuild panel assets after extension install/remove.',
            'type' => 'boolean',
            'default' => true,
        ],
        'cache_ttl' => [
            'label' => 'Cache TTL (minutes)',
            'description' => 'How long to cache extension data before refreshing.',
            'type' => 'integer',
            'default' => 60,
        ],
    ],
];
