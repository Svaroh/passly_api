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

use Cake\Http\Exception\GoneException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Sync\Model\Dto\SyncChangesDto;
use Passbolt\Sync\Model\Table\SyncLogTable;

/**
 * Turns journal events into the delta a specific user is allowed to see.
 *
 * The journal only says "this entity changed". Whether the caller may still see it is decided here, at read time,
 * against current permissions. That is what lets a revoked permission surface as a deletion: the resource is still
 * very much alive on the server, it is simply no longer visible to this user, and for the client those two are the
 * same thing.
 */
class SyncChangesService
{
    use LocatorAwareTrait;

    /**
     * @param string $userId Caller.
     * @param int $since Cursor the client last applied.
     * @param int $limit Maximum number of journal events to consume.
     * @return \Passbolt\Sync\Model\Dto\SyncChangesDto
     * @throws \Cake\Http\Exception\GoneException If the cursor predates the retained journal.
     */
    public function get(string $userId, int $since, int $limit): SyncChangesDto
    {
        /** @var \Passbolt\Sync\Model\Table\SyncLogTable $syncLog */
        $syncLog = $this->fetchTable('Passbolt/Sync.SyncLog');

        $this->assertCursorIsResumable($syncLog, $since);

        $watermark = $syncLog->findWatermark();
        if ($watermark <= $since) {
            // nothing settled since the client last asked
            return new SyncChangesDto($since, false, [], []);
        }

        $events = $syncLog->find()
            ->select(['seq', 'entity_type', 'entity_id'])
            ->where(['seq >' => $since, 'seq <=' => $watermark])
            ->orderBy(['seq' => 'ASC'])
            ->limit($limit)
            ->disableHydration()
            ->toArray();

        if (empty($events)) {
            return new SyncChangesDto($since, false, [], []);
        }

        $cursor = (int)$events[array_key_last($events)]['seq'];
        $hasMore = $cursor < $watermark;

        return $this->resolve($userId, $events, $cursor, $hasMore);
    }

    /**
     * A cursor below the oldest retained event cannot be turned into a delta: the events it would need were pruned.
     * The client is told to bootstrap instead, which never costs it local data.
     *
     * @param \Passbolt\Sync\Model\Table\SyncLogTable $syncLog Journal.
     * @param int $since Cursor.
     * @return void
     * @throws \Cake\Http\Exception\GoneException
     */
    private function assertCursorIsResumable(SyncLogTable $syncLog, int $since): void
    {
        if ($since === 0) {
            // a client with no cursor is bootstrapping, not resuming
            return;
        }

        $oldest = $syncLog->findOldestRetainedSequence();
        if ($oldest > 0 && $since < $oldest - 1) {
            throw new GoneException(__('The synchronisation cursor is older than the retained change journal.'));
        }
    }

    /**
     * @param string $userId Caller.
     * @param array<array<string, mixed>> $events Journal rows.
     * @param int $cursor Cursor to report back.
     * @param bool $hasMore Whether more settled events remain.
     * @return \Passbolt\Sync\Model\Dto\SyncChangesDto
     */
    private function resolve(string $userId, array $events, int $cursor, bool $hasMore): SyncChangesDto
    {
        $touched = [];
        foreach ($events as $event) {
            $touched[$event['entity_type']][$event['entity_id']] = true;
        }

        $upserts = [];
        $deletions = [];

        $resourceIds = array_keys($touched[SyncLogTable::ENTITY_TYPE_RESOURCE] ?? []);
        if (!empty($resourceIds)) {
            [$visible, $gone] = $this->partitionResources($userId, $resourceIds);
            $upserts['resources'] = $visible;
            $deletions['resources'] = $gone;
        }

        // A secret event is only meaningful together with its resource, and the resource is what carries the
        // permission, so secrets are resolved through the resources the caller can still see.
        $secretResourceIds = array_keys($touched[SyncLogTable::ENTITY_TYPE_SECRET] ?? []);
        if (!empty($secretResourceIds)) {
            $upserts['secrets'] = $this->visibleSecrets($userId, $secretResourceIds);
        }

        return new SyncChangesDto($cursor, $hasMore, $upserts, $deletions);
    }

    /**
     * @param string $userId Caller.
     * @param array<string> $resourceIds Candidate resource ids.
     * @return array{0: array<array<string, mixed>>, 1: array<string>} Visible resources, then ids that are gone.
     */
    private function partitionResources(string $userId, array $resourceIds): array
    {
        /** @var \App\Model\Table\ResourcesTable $resourcesTable */
        $resourcesTable = $this->fetchTable('Resources');

        $visible = $resourcesTable
            ->findIndex($userId, [
                'filter' => ['has-id' => $resourceIds],
                'contain' => ['permission' => true, 'permissions' => true, 'favorite' => true],
            ])
            ->disableHydration()
            ->toArray();

        $visibleIds = array_column($visible, 'id');
        $gone = array_values(array_diff($resourceIds, $visibleIds));

        return [$visible, $gone];
    }

    /**
     * @param string $userId Caller.
     * @param array<string> $resourceIds Resources whose secret changed.
     * @return array<array<string, mixed>>
     */
    private function visibleSecrets(string $userId, array $resourceIds): array
    {
        /** @var \App\Model\Table\ResourcesTable $resourcesTable */
        $resourcesTable = $this->fetchTable('Resources');

        $visibleIds = $resourcesTable
            ->findIndex($userId, ['filter' => ['has-id' => $resourceIds]])
            ->select(['Resources.id'])
            ->disableHydration()
            ->all()
            ->extract('id')
            ->toArray();

        if (empty($visibleIds)) {
            return [];
        }

        return $this->fetchTable('Secrets')
            ->find()
            ->select(['id', 'resource_id', 'data', 'modified'])
            ->where(['user_id' => $userId, 'resource_id IN' => $visibleIds])
            ->disableHydration()
            ->toArray();
    }
}
