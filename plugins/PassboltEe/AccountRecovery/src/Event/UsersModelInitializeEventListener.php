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

namespace Passbolt\AccountRecovery\Event;

use App\Model\Table\UsersTable;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Passbolt\AccountRecovery\Model\Entity\AccountRecoveryRequest;

class UsersModelInitializeEventListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.initialize' => [
                'addAccountRecoveryUserSettingsAssociation',
                'addAccountRecoveryPrivateKeysAssociation',
                'addPendingAccountRecoveryRequestsAssociation',
            ],
        ];
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addAccountRecoveryUserSettingsAssociation(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof UsersTable || $table->hasAssociation('AccountRecoveryUserSettings')) {
            return;
        }

        $table->hasOne('Passbolt/AccountRecovery.AccountRecoveryUserSettings');
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addAccountRecoveryPrivateKeysAssociation(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof UsersTable || $table->hasAssociation('AccountRecoveryPrivateKeys')) {
            return;
        }

        $table->hasOne('Passbolt/AccountRecovery.AccountRecoveryPrivateKeys');
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addPendingAccountRecoveryRequestsAssociation(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof UsersTable || $table->hasAssociation('PendingAccountRecoveryRequests')) {
            return;
        }

        $table->hasOne('PendingAccountRecoveryRequests', [
            'className' => 'Passbolt/AccountRecovery.AccountRecoveryRequests',
            'foreignKey' => 'user_id',
            'conditions' => [
                'PendingAccountRecoveryRequests.status' => AccountRecoveryRequest::ACCOUNT_RECOVERY_REQUEST_PENDING,
            ],
        ]);
    }
}
