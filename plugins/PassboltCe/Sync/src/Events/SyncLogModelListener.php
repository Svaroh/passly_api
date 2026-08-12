<?php
declare(strict_types=1);

/**
 * Passly ~ Open source password manager for teams
 * Copyright (c) Svaroh
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Svaroh
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://passly.svaroh.net Passly
 * @since         6.0.0
 */

namespace Passbolt\Sync\Events;

use App\Model\Table\PermissionsTable;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Sync\Model\Table\SyncLogTable;
use Passbolt\Sync\Service\SyncLogRecorderService;

/**
 * Turns model writes into change journal events.
 *
 * This listens on the global model event manager rather than patching core table classes, so the whole journal
 * lives inside this plugin and the core stays untouched.
 */
class SyncLogModelListener implements EventListenerInterface
{
    use LocatorAwareTrait;

    /**
     * Table alias to journal entity type, for the entities a client mirrors one to one.
     *
     * @var array<string, string>
     */
    private const DIRECT_ENTITY_TYPES = [
        'Resources' => SyncLogTable::ENTITY_TYPE_RESOURCE,
        'Folders' => SyncLogTable::ENTITY_TYPE_FOLDER,
        'Secrets' => SyncLogTable::ENTITY_TYPE_SECRET,
        'Users' => SyncLogTable::ENTITY_TYPE_USER,
        'Groups' => SyncLogTable::ENTITY_TYPE_GROUP,
        'Tags' => SyncLogTable::ENTITY_TYPE_TAG,
        'ResourceTypes' => SyncLogTable::ENTITY_TYPE_RESOURCE_TYPE,
        'MetadataKeys' => SyncLogTable::ENTITY_TYPE_METADATA_KEY,
    ];

    private SyncLogRecorderService $recorder;

    /**
     * @param \Passbolt\Sync\Service\SyncLogRecorderService|null $recorder Journal writer.
     */
    public function __construct(?SyncLogRecorderService $recorder = null)
    {
        $this->recorder = $recorder ?? new SyncLogRecorderService();
    }

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.afterSave' => 'recordSave',
            'Model.afterDelete' => 'recordDelete',
        ];
    }

    /**
     * @param \Cake\Event\Event $event Model event.
     * @return void
     */
    public function recordSave(Event $event): void
    {
        $this->record($event, SyncLogTable::OP_UPSERT);
    }

    /**
     * @param \Cake\Event\Event $event Model event.
     * @return void
     */
    public function recordDelete(Event $event): void
    {
        $this->record($event, SyncLogTable::OP_DELETE);
    }

    /**
     * @param \Cake\Event\Event $event Model event.
     * @param string $op Journal operation.
     * @return void
     */
    private function record(Event $event, string $op): void
    {
        $alias = $event->getSubject()->getAlias();
        /** @var \Cake\Datasource\EntityInterface $entity */
        $entity = $event->getData('entity');

        if (isset(self::DIRECT_ENTITY_TYPES[$alias])) {
            $this->recordDirect($alias, $entity, $op);

            return;
        }

        // Permissions and group memberships have no client side counterpart. What changes for a client is which
        // resources and folders it may see, so the event is translated onto those instead.
        if ($alias === 'Permissions') {
            $this->recordPermission($entity);

            return;
        }

        if ($alias === 'GroupsUsers') {
            $this->recordGroupMembership($entity);
        }
    }

    /**
     * @param string $alias Table alias.
     * @param \Cake\Datasource\EntityInterface $entity Saved or deleted entity.
     * @param string $op Journal operation.
     * @return void
     */
    private function recordDirect(string $alias, EntityInterface $entity, string $op): void
    {
        $id = $entity->get('id');
        if (!is_string($id)) {
            return;
        }

        // Resources, folders and secrets are soft deleted, and a soft delete arrives as a save. For a client
        // "deleted" and "no longer visible" are the same thing, so both become a delete event.
        if ($op === SyncLogTable::OP_UPSERT && $this->isSoftDeleted($entity)) {
            $op = SyncLogTable::OP_DELETE;
        }

        $this->recorder->record(self::DIRECT_ENTITY_TYPES[$alias], $id, $op);
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity Permission entity.
     * @return void
     */
    private function recordPermission(EntityInterface $entity): void
    {
        $aco = $entity->get('aco');
        $acoForeignKey = $entity->get('aco_foreign_key');
        if (!is_string($acoForeignKey)) {
            return;
        }

        $entityType = match ($aco) {
            PermissionsTable::RESOURCE_ACO => SyncLogTable::ENTITY_TYPE_RESOURCE,
            PermissionsTable::FOLDER_ACO => SyncLogTable::ENTITY_TYPE_FOLDER,
            default => null,
        };

        if ($entityType === null) {
            return;
        }

        // Always an upsert: whether the change granted or revoked access is resolved per user at read time.
        $this->recorder->record($entityType, $acoForeignKey, SyncLogTable::OP_UPSERT);
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity GroupsUser entity.
     * @return void
     */
    private function recordGroupMembership(EntityInterface $entity): void
    {
        $groupId = $entity->get('group_id');
        if (!is_string($groupId)) {
            return;
        }

        $permissions = $this->fetchTable('Permissions')
            ->find()
            ->select(['aco', 'aco_foreign_key'])
            ->where([
                'aro' => PermissionsTable::GROUP_ARO,
                'aro_foreign_key' => $groupId,
            ])
            ->disableHydration()
            ->toArray();

        $resourceIds = [];
        $folderIds = [];
        foreach ($permissions as $permission) {
            if ($permission['aco'] === PermissionsTable::RESOURCE_ACO) {
                $resourceIds[] = $permission['aco_foreign_key'];
            } elseif ($permission['aco'] === PermissionsTable::FOLDER_ACO) {
                $folderIds[] = $permission['aco_foreign_key'];
            }
        }

        $this->recorder->recordMany(SyncLogTable::ENTITY_TYPE_RESOURCE, $resourceIds, SyncLogTable::OP_UPSERT);
        $this->recorder->recordMany(SyncLogTable::ENTITY_TYPE_FOLDER, $folderIds, SyncLogTable::OP_UPSERT);
        $this->recorder->record(SyncLogTable::ENTITY_TYPE_GROUP, $groupId, SyncLogTable::OP_UPSERT);
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity Entity to inspect.
     * @return bool
     */
    private function isSoftDeleted(EntityInterface $entity): bool
    {
        if (!$entity->has('deleted')) {
            return false;
        }

        $deleted = $entity->get('deleted');

        // resources use a boolean flag, secrets and metadata keys use a nullable timestamp
        return $deleted === true || (!is_bool($deleted) && $deleted !== null);
    }
}
