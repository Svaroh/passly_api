<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         4.0.0
 */
namespace Passbolt\ResourceTypes\Model\Definition;

class SlugDefinition
{
    private static array $nameMetadataPropertySchemaV5 = [
        'type' => 'string',
        'maxLength' => 255,
    ];

    private static array $usernameMetadataPropertySchemaV5 = [
        'anyOf' => [
            [
                'type' => 'string',
                'maxLength' => 255,
            ],
            [
                'type' => 'null',
            ],
        ],
    ];

    private static array $urisMetadataPropertySchemaV5 = [
        'type' => 'array',
        'items' => [
            'anyOf' => [
                ['type' => 'string', 'maxLength' => 1024],
                ['type' => 'null'],
            ],
        ],
        'maxItems' => 32,
    ];

    private static array $descriptionMetadataPropertySchemaV5 = [
        'anyOf' => [
            ['type' => 'string', 'maxLength' => 10000],
            ['type' => 'null'],
        ],
    ];

    private static array $iconMetadataPropertySchemaV5 = [
        'type' => 'object',
        'required' => [],
        'properties' => [
            'type' => [
                'type' => 'string',
                'enum' => ['keepass-icon-set', 'passbolt-icon-set'],
            ],
            'value' => [
                'type' => 'integer',
                'minimum' => 0,
            ],
            'background_color' => [
                'anyOf' => [
                    [
                        'type' => 'string',
                        'pattern' => '^#(?:[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$',
                    ],
                    ['type' => 'null'],
                ],
            ],
        ],
    ];

    private static array $objectTypeSecretPropertySchemaV5 = [
        'type' => 'string',
        'enum' => ['PASSBOLT_SECRET_DATA'],
    ];

    private static array $passwordSecretPropertySchemaV5 = [
        'type' => 'string',
        'maxLength' => 4096,
    ];

    private static array $descriptionSecretPropertySchemaV5 = [
        'anyOf' => [
            ['type' => 'string', 'maxLength' => 50000],
            ['type' => 'null'],
        ],
    ];

    private static array $totpSecretPropertySchemaV5 = [
        'type' => 'object',
        'required' => ['secret_key', 'digits', 'algorithm'],
        'properties' => [
            'algorithm' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 6,
            ],
            'secret_key' => [
                'type' => 'string',
                'maxLength' => 1024,
            ],
            'digits' => [
                'type' => 'number',
                'minimum' => 6,
                'exclusiveMaximum' => 9,
            ],
            'period' => [
                'type' => 'number',
            ],
        ],
    ];

    private static array $customFieldMetadataPropertySchemaV5 = [
        'type' => 'array',
        'maxItems' => 128,
        'items' => [
            'type' => 'object',
            'required' => ['id', 'type'],
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'format' => 'uuid',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['text', 'password', 'boolean', 'number', 'uri'],
                ],
                'metadata_key' => [
                    'anyOf' => [
                        ['type' => 'string', 'maxLength' => 255],
                        ['type' => 'null'],
                    ],
                ],
                'metadata_value' => [
                    'anyOf' => [
                        ['type' => 'string', 'maxLength' => 20000],
                        ['type' => 'number'],
                        ['type' => 'boolean'],
                        ['type' => 'null'],
                    ],
                ],
            ],
        ],
    ];

    private static array $customFieldSecretPropertySchemaV5 = [
        'type' => 'array',
        'maxItems' => 128,
        'items' => [
            'type' => 'object',
            'required' => ['id', 'type'],
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'format' => 'uuid',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['text', 'password', 'boolean', 'number', 'uri'],
                ],
                'secret_key' => [
                    'anyOf' => [
                        ['type' => 'string', 'maxLength' => 255],
                        ['type' => 'null'],
                    ],
                ],
                'secret_value' => [
                    'anyOf' => [
                        ['type' => 'string', 'maxLength' => 20000],
                        ['type' => 'number'],
                        ['type' => 'boolean'],
                        ['type' => 'null'],
                    ],
                ],
            ],
        ],
    ];

    private static array $pinCodeSecretPropertySchemaV5 = [
        'type' => 'string',
        'minLength' => 4,
        'maxLength' => 12,
        'pattern' => '^\d+$',
    ];

    private static array $passkeyBase64UrlSecretPropertySchemaV5 = [
        'type' => 'string',
        'maxLength' => 8192,
        'pattern' => '^[A-Za-z0-9_-]+$',
    ];

    private static array $passkeyObjectTypeSecretPropertySchemaV5 = [
        'type' => 'string',
        'enum' => ['PASSLY_PASSKEY'],
    ];

    /**
     * @return string|false
     */
    public static function passwordString(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                    ],
                    'username' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 255,
                            ],
                            [
                                'type' => null,
                            ],
                        ],
                    ],
                    'uri' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 1024,
                            ],
                            [
                                'type' => 'null',
                            ],
                        ],
                    ],
                    'description' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 10000,
                            ],
                            [
                                'type' => 'null',
                            ],
                        ],
                    ],
                ],
            ],
            'secret' => [
                'type' => 'string',
                'maxLength' => 4096,
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function passwordAndDescription(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => [
                    'name',
                ],
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                    ],
                    'username' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 255,
                            ],
                            [
                                'type' => 'null',
                            ],
                        ],
                    ],
                    'uri' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 1024,
                            ],
                            [
                                'type' => 'null',
                            ],
                        ],
                    ],
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'password',
                ],
                'properties' => [
                    'password' => [
                        'type' => 'string',
                        'maxLength' => 4096,
                    ],
                    'description' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 10000,
                            ],
                            [
                                'type' => 'null',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function totp(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => [
                    'name',
                ],
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                    ],
                    'uri' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'maxLength' => 1024,
                            ],
                            [
                                'type' => 'null',
                            ],
                        ],
                    ],
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'totp',
                ],
                'properties' => [
                    'totp' => [
                        'type' => 'object',
                        'required' => ['secret_key', 'digits', 'algorithm'],
                        'properties' => [
                            'algorithm' => [
                                'type' => 'string',
                                'minLength' => 4,
                                'maxLength' => 6,
                            ],
                            'secret_key' => [
                                'type' => 'string',
                                'maxLength' => 1024,
                            ],
                            'digits' => [
                                'type' => 'number',
                                'minimum' => 6,
                                'exclusiveMaximum' => 9,
                            ],
                            'period' => [
                                'type' => 'number',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function passwordDescriptionTotp(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                    ],
                    'username' => [
                        'anyOf' => [
                            ['type' => 'string', 'maxLength' => 255],
                            ['type' => 'null'],
                        ],
                    ],
                    'uri' => [
                        'anyOf' => [
                            ['type' => 'string', 'maxLength' => 1024],
                            ['type' => 'null'],
                        ],
                    ],
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => ['password', 'totp'],
                'properties' => [
                    'password' => [
                        'type' => 'string',
                        'maxLength' => 4096,
                    ],
                    'description' => [
                        'anyOf' => [
                            ['type' => 'string', 'maxLength' => 10000],
                            ['type' => 'null'],
                        ],
                    ],
                    'totp' => [
                        'type' => 'object',
                        'required' => ['secret_key', 'digits', 'algorithm'],
                        'properties' => [
                            'algorithm' => [
                                'type' => 'string',
                                'minLength' => 4,
                                'maxLength' => 6,
                            ],
                            'secret_key' => [
                                'type' => 'string',
                                'maxLength' => 1024,
                            ],
                            'digits' => [
                                'type' => 'number',
                                'minimum' => 6,
                                'exclusiveMaximum' => 9,
                            ],
                            'period' => [
                                'type' => 'number',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5PasswordString(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'username' => self::$usernameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'string',
                'maxLength' => 4096,
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5Default(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => [
                    'name',
                ],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'username' => self::$usernameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                    'custom_fields' => self::$customFieldMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'password',
                ],
                'properties' => [
                    'object_type' => self::$objectTypeSecretPropertySchemaV5,
                    'password' => self::$passwordSecretPropertySchemaV5,
                    'description' => self::$descriptionSecretPropertySchemaV5,
                    'custom_fields' => self::$customFieldSecretPropertySchemaV5,
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5DefaultTotp(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => [
                    'name',
                ],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'username' => self::$usernameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                    'custom_fields' => self::$customFieldMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'password',
                    'totp',
                ],
                'properties' => [
                    'object_type' => self::$objectTypeSecretPropertySchemaV5,
                    'password' => self::$passwordSecretPropertySchemaV5,
                    'description' => self::$descriptionSecretPropertySchemaV5,
                    'totp' => self::$totpSecretPropertySchemaV5,
                    'custom_fields' => self::$customFieldSecretPropertySchemaV5,
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5Totp(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => [
                    'name',
                ],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'totp',
                ],
                'properties' => [
                    'object_type' => self::$objectTypeSecretPropertySchemaV5,
                    'totp' => self::$totpSecretPropertySchemaV5,
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5CustomFields(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => [
                    'name',
                    'custom_fields',
                ],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                    'custom_fields' => self::$customFieldMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'object_type',
                    'custom_fields',
                ],
                'properties' => [
                    'object_type' => self::$objectTypeSecretPropertySchemaV5,
                    'custom_fields' => self::$customFieldSecretPropertySchemaV5,
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5Note(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'description',
                    'object_type',
                ],
                'properties' => [
                    'object_type' => self::$objectTypeSecretPropertySchemaV5,
                    'description' => self::$descriptionSecretPropertySchemaV5,
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5PinCode(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'pin_code',
                    'object_type',
                ],
                'properties' => [
                    'object_type' => self::$objectTypeSecretPropertySchemaV5,
                    'pin_code' => self::$pinCodeSecretPropertySchemaV5,
                    'description' => self::$descriptionSecretPropertySchemaV5,
                ],
            ],
        ]);
    }

    /**
     * @return string|false
     */
    public static function v5Passkey(): string|false
    {
        return json_encode([
            'resource' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => self::$nameMetadataPropertySchemaV5,
                    'username' => self::$usernameMetadataPropertySchemaV5,
                    'uris' => self::$urisMetadataPropertySchemaV5,
                    'description' => self::$descriptionMetadataPropertySchemaV5,
                    'icon' => self::$iconMetadataPropertySchemaV5,
                ],
            ],
            'secret' => [
                'type' => 'object',
                'required' => [
                    'object_type',
                    'schema_version',
                    'credential_id',
                    'rp_id',
                    'user_handle',
                    'user_name',
                    'cose_alg',
                    'public_key_cose',
                    'private_key_pkcs8',
                    'aaguid',
                    'backup_eligible',
                    'backup_state',
                    'sign_count',
                ],
                'properties' => [
                    'object_type' => self::$passkeyObjectTypeSecretPropertySchemaV5,
                    'schema_version' => [
                        'type' => 'integer',
                        'enum' => [1],
                    ],
                    'credential_id' => self::$passkeyBase64UrlSecretPropertySchemaV5,
                    'rp_id' => [
                        'type' => 'string',
                        'maxLength' => 253,
                    ],
                    'origin' => [
                        'anyOf' => [
                            ['type' => 'string', 'maxLength' => 1024],
                            ['type' => 'null'],
                        ],
                    ],
                    'user_handle' => self::$passkeyBase64UrlSecretPropertySchemaV5,
                    'user_name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                    ],
                    'user_display_name' => [
                        'anyOf' => [
                            ['type' => 'string', 'maxLength' => 255],
                            ['type' => 'null'],
                        ],
                    ],
                    'cose_alg' => [
                        'type' => 'integer',
                        'enum' => [-7],
                    ],
                    'public_key_cose' => self::$passkeyBase64UrlSecretPropertySchemaV5,
                    'private_key_pkcs8' => self::$passkeyBase64UrlSecretPropertySchemaV5,
                    'aaguid' => [
                        'type' => 'string',
                        'format' => 'uuid',
                    ],
                    'backup_eligible' => [
                        'type' => 'boolean',
                    ],
                    'backup_state' => [
                        'type' => 'boolean',
                    ],
                    'sign_count' => [
                        'type' => 'integer',
                        'minimum' => 0,
                    ],
                    'transports' => [
                        'type' => 'array',
                        'maxItems' => 8,
                        'items' => [
                            'type' => 'string',
                            'enum' => ['ble', 'hybrid', 'internal', 'nfc', 'usb'],
                        ],
                    ],
                    'extensions' => [
                        'type' => 'object',
                        'required' => [],
                        'properties' => [],
                    ],
                    'description' => self::$descriptionSecretPropertySchemaV5,
                ],
            ],
        ]);
    }
}
