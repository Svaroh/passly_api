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

namespace Passbolt\Sync\Service;

use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Throwable;

/**
 * Writes change events to the journal.
 *
 * Recording is best effort on purpose. A journal write runs inside the transaction of the operation that triggered
 * it, so letting it throw would mean a failed audit-shaped side table could roll back a user's edit. A missed event
 * costs freshness until the next reconciliation pass, which the protocol runs daily and on every manual sync
 * precisely because no journal is immune to gaps (see docs/sync-protocol.md, "Reconciliation").
 */
class SyncLogRecorderService
{
    use LocatorAwareTrait;

    /**
     * @param string $entityType One of \Passbolt\Sync\Model\Table\SyncLogTable::ENTITY_TYPES.
     * @param string $entityId Entity uuid.
     * @param string $op One of \Passbolt\Sync\Model\Table\SyncLogTable::OPS.
     * @return void
     */
    public function record(string $entityType, string $entityId, string $op): void
    {
        $this->recordMany($entityType, [$entityId], $op);
    }

    /**
     * @param string $entityType One of \Passbolt\Sync\Model\Table\SyncLogTable::ENTITY_TYPES.
     * @param array<string> $entityIds Entity uuids, duplicates are collapsed.
     * @param string $op One of \Passbolt\Sync\Model\Table\SyncLogTable::OPS.
     * @return void
     */
    public function recordMany(string $entityType, array $entityIds, string $op): void
    {
        $entityIds = array_values(array_unique(array_filter($entityIds)));
        if (empty($entityIds)) {
            return;
        }

        try {
            $table = $this->fetchTable('Passbolt/Sync.SyncLog');
            $now = DateTime::now();

            $entities = [];
            foreach ($entityIds as $entityId) {
                $entities[] = $table->newEntity(
                    [
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'op' => $op,
                        'created' => $now,
                    ],
                    ['accessibleFields' => [
                        'entity_type' => true,
                        'entity_id' => true,
                        'op' => true,
                        'created' => true,
                    ]]
                );
            }

            $table->saveMany($entities, ['atomic' => false]);
        } catch (Throwable $e) {
            Log::error(sprintf(
                'Could not record %d sync journal %s event(s) of type %s: %s',
                count($entityIds),
                $op,
                $entityType,
                $e->getMessage()
            ));
        }
    }
}
