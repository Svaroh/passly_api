<?php
declare(strict_types=1);

/**
 * Passly ~ Open source password manager for teams
 * Copyright (c) Svaroh (https://passly.svaroh.net)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Svaroh (https://passly.svaroh.net)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://passly.svaroh.net Passly
 */
use App\Model\Entity\OrganizationSetting;
use App\Utility\UuidFactory;
use Migrations\AbstractMigration;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;
use Passbolt\OfflineMode\Model\Entity\OfflineModeSetting;

class V602SetPerpetualOfflineModeSettings extends AbstractMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        $propertyId = UuidFactory::uuid(
            OrganizationSetting::UUID_NAMESPACE . OfflineModeSetting::PROPERTY_NAME,
        );

        $existing = $this->fetchRow(
            sprintf("SELECT id FROM organization_settings WHERE property_id = '%s' LIMIT 1", $propertyId),
        );

        $value = json_encode([
            'max_session_duration' => OfflineSettingsDto::DEFAULT_MAX_SESSION_DURATION,
            'data_retention_period' => OfflineSettingsDto::DEFAULT_DATA_RETENTION_PERIOD,
            'max_items' => OfflineSettingsDto::DEFAULT_MAX_ITEMS,
        ]);
        $now = date('Y-m-d H:i:s');

        if (!empty($existing)) {
            $this->execute(
                sprintf(
                    "UPDATE organization_settings SET value = '%s', modified = '%s' WHERE id = '%s'",
                    addslashes($value),
                    $now,
                    $existing['id'],
                ),
            );
        } else {
            $admin = $this->fetchRow('SELECT id FROM users ORDER BY created ASC LIMIT 1');
            $adminId = $admin['id'] ?? UuidFactory::uuid('users.system');

            $table = $this->table('organization_settings');
            $table->insert([
                'id' => UuidFactory::uuid(),
                'property' => OfflineModeSetting::PROPERTY_NAME,
                'property_id' => $propertyId,
                'value' => $value,
                'created' => $now,
                'created_by' => $adminId,
                'modified' => $now,
                'modified_by' => $adminId,
            ])->saveData();
        }
    }
}
