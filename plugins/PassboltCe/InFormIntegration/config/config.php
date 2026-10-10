<?php

use Cake\Core\Configure;

return [
    'passbolt' => [
        'plugins' => [
            'inFormIntegration' => [
                'enabled' => Configure::read(
                    'passbolt.plugins.inFormIntegration.enabled',
                    filter_var(env('PASSBOLT_PLUGINS_IN_FORM_INTEGRATION_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
                ),
                'version' => '1.0.0',
                'settingsVisibility' => [
                    'whiteListPublic' => [
                        'enabled',
                    ],
                ],
            ],
        ],
    ],
];
