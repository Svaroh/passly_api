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

namespace Passbolt\MultiFactorAuthentication\Event;

use App\Model\Table\UsersTable;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Passbolt\MultiFactorAuthentication\Service\Query\IsMfaEnabledQueryService;
use Passbolt\MultiFactorAuthentication\Utility\MfaSettings;

class UsersModelInitializeEventListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.initialize' => 'addMfaSettingsAssociation',
        ];
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addMfaSettingsAssociation(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof UsersTable || $table->hasAssociation('MfaSettings')) {
            return;
        }

        $table->hasOne('MfaSettings')
            ->setClassName('Passbolt/AccountSettings.AccountSettings')
            ->setForeignKey('user_id')
            ->setProperty(IsMfaEnabledQueryService::MFA_SETTINGS_PROPERTY)
            ->setConditions(['MfaSettings.property' => MfaSettings::MFA]);
    }
}
