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

namespace Passbolt\Folders\EventListener;

use App\Model\Table\ResourcesTable;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Passbolt\Folders\Model\Behavior\FolderizableBehavior;

class ResourcesModelInitializeEventListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.initialize' => [
                'addFoldersRelationsAssociation',
                'addFolderizableBehavior',
            ],
        ];
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addFoldersRelationsAssociation(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof ResourcesTable || $table->hasAssociation('FoldersRelations')) {
            return;
        }

        $table->hasMany('FoldersRelations', [
            'className' => 'Passbolt/Folders.FoldersRelations',
            'foreignKey' => 'foreign_id',
            'conditions' => [
                'FoldersRelations.foreign_model' => 'Resource',
            ],
            'dependent' => true,
        ]);
    }

    /**
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function addFolderizableBehavior(EventInterface $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof ResourcesTable || $table->behaviors()->has('Folderizable')) {
            return;
        }

        $table->addBehavior(FolderizableBehavior::class);
    }
}
