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

namespace Passbolt\Sync\Test\TestCase\Controller;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\UuidFactory;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Passbolt\Sync\Model\Table\SyncLogTable;
use Passbolt\Sync\SyncPlugin;

class SyncChangesControllerTest extends AppIntegrationTestCase
{
    private SyncLogTable $SyncLog;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(SyncPlugin::class);
        /** @var \Passbolt\Sync\Model\Table\SyncLogTable $syncLog */
        $syncLog = TableRegistry::getTableLocator()->get('Passbolt/Sync.SyncLog');
        $this->SyncLog = $syncLog;
        $this->SyncLog->deleteAll([]);
    }

    /**
     * Journal rows are written straight here so a test controls the sequence and the age of an event, which is what
     * the watermark and the retention checks turn on.
     */
    private function journalEvent(string $entityType, string $entityId, string $op, int $ageSeconds = 60): void
    {
        $this->SyncLog->saveOrFail($this->SyncLog->newEntity(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'op' => $op,
                'created' => DateTime::now()->subSeconds($ageSeconds),
            ],
            ['accessibleFields' => [
                'entity_type' => true,
                'entity_id' => true,
                'op' => true,
                'created' => true,
            ]]
        ));
    }

    public function testSyncChangesController_Error_NotAuthenticated(): void
    {
        $this->getJson('/sync/changes.json');
        $this->assertAuthenticationError();
    }

    public function testSyncChangesController_Error_InvalidCursor(): void
    {
        $this->logInAsUser();
        $this->getJson('/sync/changes.json?since=-1');
        $this->assertBadRequestError('The cursor should be a positive integer.');
    }

    public function testSyncChangesController_Error_LimitAboveMaximum(): void
    {
        $this->logInAsUser();
        $this->getJson('/sync/changes.json?limit=100000');
        $this->assertResponseCode(400);
    }

    public function testSyncChangesController_Success_ReportsAccessibleResourceAsUpsert(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withPermissionsFor([$user])->persist();
        $this->SyncLog->deleteAll([]);
        $this->journalEvent(SyncLogTable::ENTITY_TYPE_RESOURCE, $resource->get('id'), SyncLogTable::OP_UPSERT);

        $this->logInAs($user);
        $this->getJson('/sync/changes.json');
        $this->assertSuccess();

        $upserted = array_column((array)($this->_responseJsonBody->upserts->resources ?? []), 'id');
        $this->assertContains($resource->get('id'), $upserted);
        $this->assertGreaterThan(0, $this->_responseJsonBody->cursor);
    }

    public function testSyncChangesController_Success_ReportsInaccessibleResourceAsDeletion(): void
    {
        $user = UserFactory::make()->user()->persist();
        // touched in the journal, but this user never had a permission on it
        $foreignResource = ResourceFactory::make()->persist();
        $this->SyncLog->deleteAll([]);
        $this->journalEvent(SyncLogTable::ENTITY_TYPE_RESOURCE, $foreignResource->get('id'), SyncLogTable::OP_UPSERT);

        $this->logInAs($user);
        $this->getJson('/sync/changes.json');
        $this->assertSuccess();

        $deleted = (array)($this->_responseJsonBody->deletions->resources ?? []);
        $this->assertContains($foreignResource->get('id'), $deleted);
        $this->assertEmpty((array)($this->_responseJsonBody->upserts->resources ?? []));
    }

    public function testSyncChangesController_Success_WithholdsEventsInsideTheWatermark(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withPermissionsFor([$user])->persist();
        $this->SyncLog->deleteAll([]);
        // younger than the watermark lag, so an in flight transaction with a lower seq could still land
        $this->journalEvent(SyncLogTable::ENTITY_TYPE_RESOURCE, $resource->get('id'), SyncLogTable::OP_UPSERT, 0);

        $this->logInAs($user);
        $this->getJson('/sync/changes.json');
        $this->assertSuccess();

        $this->assertEmpty((array)($this->_responseJsonBody->upserts->resources ?? []));
        $this->assertSame(0, $this->_responseJsonBody->cursor);
    }

    public function testSyncChangesController_Error_CursorOlderThanRetainedJournal(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->SyncLog->deleteAll([]);

        // Three events, then retention prunes the first two: a client still holding the first cursor can no longer
        // be served a delta, because the events it would need to catch up on are gone.
        for ($i = 0; $i < 3; $i++) {
            $this->journalEvent(SyncLogTable::ENTITY_TYPE_RESOURCE, UuidFactory::uuid(), SyncLogTable::OP_UPSERT);
        }
        $seqs = $this->SyncLog->find()->select(['seq'])->orderBy(['seq' => 'ASC'])->all()->extract('seq')->toArray();
        $staleCursor = (int)$seqs[0];
        $this->SyncLog->deleteAll(['seq <=' => $seqs[1]]);

        $this->logInAs($user);
        $this->getJson('/sync/changes.json?since=' . $staleCursor);
        $this->assertResponseCode(410);
    }

    public function testSyncChangesController_Success_CursorAtTheEdgeOfRetentionStillResumes(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->SyncLog->deleteAll([]);

        for ($i = 0; $i < 2; $i++) {
            $this->journalEvent(SyncLogTable::ENTITY_TYPE_RESOURCE, UuidFactory::uuid(), SyncLogTable::OP_UPSERT);
        }
        $seqs = $this->SyncLog->find()->select(['seq'])->orderBy(['seq' => 'ASC'])->all()->extract('seq')->toArray();
        // the client applied everything up to the event just before the oldest retained one, so nothing is missing
        $this->SyncLog->deleteAll(['seq <' => $seqs[1]]);

        $this->logInAs($user);
        $this->getJson('/sync/changes.json?since=' . ((int)$seqs[1] - 1));
        $this->assertSuccess();
    }
}
