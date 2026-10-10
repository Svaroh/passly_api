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

namespace Passbolt\Rbacs\Event;

use App\Model\Table\RolesTable;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Passbolt\Log\Model\Table\ActionsTable;

class RbacsModelInitializeEventListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.initialize' => [
                'addRbacsAssociationToActions',
                'addRbacsAssociationToRoles',
            ],
        ];
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addRbacsAssociationToActions(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof ActionsTable || $table->hasAssociation('Rbacs')) {
            return;
        }

        $table->hasMany('Rbacs', [
            'className' => 'Passbolt/Rbacs.Rbacs',
            'foreignKey' => 'foreign_id',
            'conditions' => [
                'Rbacs.foreign_model' => 'Action',
            ],
        ]);
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addRbacsAssociationToRoles(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof RolesTable || $table->hasAssociation('Rbacs')) {
            return;
        }

        $table->hasMany('Rbacs', [
            'className' => 'Passbolt/Rbacs.Rbacs',
            'foreignKey' => 'role_id',
        ]);
    }
}
