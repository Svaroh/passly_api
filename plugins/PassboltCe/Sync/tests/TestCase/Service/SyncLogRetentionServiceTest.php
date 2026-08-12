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

namespace Passbolt\Sync\Test\TestCase\Service;

use App\Utility\UuidFactory;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Sync\Model\Table\SyncLogTable;
use Passbolt\Sync\Service\SyncLogRetentionService;

class SyncLogRetentionServiceTest extends TestCase
{
    use TruncateDirtyTables;

    private SyncLogTable $SyncLog;

    private SyncLogRetentionService $service;

    public function setUp(): void
    {
        parent::setUp();
        /** @var \Passbolt\Sync\Model\Table\SyncLogTable $syncLog */
        $syncLog = TableRegistry::getTableLocator()->get('Passbolt/Sync.SyncLog');
        $this->SyncLog = $syncLog;
        $this->SyncLog->deleteAll([]);
        $this->service = new SyncLogRetentionService();
    }

    public function tearDown(): void
    {
        Configure::delete('passbolt.plugins.sync.retentionDays');
        unset($this->SyncLog, $this->service);
        parent::tearDown();
    }

    private function insertEvent(DateTime $created): void
    {
        $entity = $this->SyncLog->newEntity(
            [
                'entity_type' => SyncLogTable::ENTITY_TYPE_RESOURCE,
                'entity_id' => UuidFactory::uuid(),
                'op' => SyncLogTable::OP_UPSERT,
                'created' => $created,
            ],
            ['accessibleFields' => [
                'entity_type' => true,
                'entity_id' => true,
                'op' => true,
                'created' => true,
            ]]
        );
        $this->SyncLog->saveOrFail($entity);
    }

    public function testPruneRemovesOnlyEntriesOlderThanTheRetentionWindow(): void
    {
        Configure::write('passbolt.plugins.sync.retentionDays', 90);

        $this->insertEvent(DateTime::now()->subDays(91));
        $this->insertEvent(DateTime::now()->subDays(120));
        $this->insertEvent(DateTime::now()->subDays(89));
        $this->insertEvent(DateTime::now());

        $pruned = $this->service->prune();

        $this->assertSame(2, $pruned);
        $this->assertSame(2, $this->SyncLog->find()->count());
    }

    public function testRetentionWindowIsConfigurable(): void
    {
        Configure::write('passbolt.plugins.sync.retentionDays', 7);

        $this->insertEvent(DateTime::now()->subDays(8));
        $this->insertEvent(DateTime::now()->subDays(6));

        $this->assertSame(7, $this->service->retentionDays());
        $this->assertSame(1, $this->service->prune());
        $this->assertSame(1, $this->SyncLog->find()->count());
    }

    public function testRetentionFallsBackToTheDefaultWindow(): void
    {
        Configure::delete('passbolt.plugins.sync.retentionDays');

        $this->assertSame(SyncLogRetentionService::DEFAULT_RETENTION_DAYS, $this->service->retentionDays());
    }
}
