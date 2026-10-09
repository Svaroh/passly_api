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
 * @since         5.15.0
 */
namespace App\Model\Dto\Settings;

readonly class SettingsDto
{
    /**
     * @param array $appSettings
     * @param array $passboltSettings
     */
    public function __construct(
        public array $appSettings,
        public array $passboltSettings,
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'app' => $this->appSettings,
            'passbolt' => $this->passboltSettings,
        ];
    }
}
