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

namespace Passbolt\Sync\Test\TestCase\Events;

use App\Model\Table\PermissionsTable;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\PermissionFactory;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Sync\Model\Table\SyncLogTable;
use Passbolt\Sync\SyncPlugin;

class SyncLogModelListenerTest extends TestCase
{
    use TruncateDirtyTables;

    private SyncLogTable $SyncLog;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins([SyncPlugin::class]);
        /** @var \Passbolt\Sync\Model\Table\SyncLogTable $syncLog */
        $syncLog = TableRegistry::getTableLocator()->get('Passbolt/Sync.SyncLog');
        $this->SyncLog = $syncLog;
        $this->SyncLog->deleteAll([]);
    }

    public function tearDown(): void
    {
        unset($this->SyncLog);
        parent::tearDown();
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function journal(): array
    {
        return $this->SyncLog->find()
            ->select(['entity_type', 'entity_id', 'op'])
            ->orderBy(['seq' => 'ASC'])
            ->disableHydration()
            ->toArray();
    }

    public function testSavingAResourceRecordsAnUpsert(): void
    {
        $resource = ResourceFactory::make()->persist();

        $this->assertContains(
            [
                'entity_type' => SyncLogTable::ENTITY_TYPE_RESOURCE,
                'entity_id' => $resource->get('id'),
                'op' => SyncLogTable::OP_UPSERT,
            ],
            $this->journal()
        );
    }

    public function testSoftDeletingAResourceRecordsADelete(): void
    {
        $resource = ResourceFactory::make()->persist();
        $resourcesTable = TableRegistry::getTableLocator()->get('Resources');

        $resource->set('deleted', true);
        $resourcesTable->saveOrFail($resource);

        $this->assertContains(
            [
                'entity_type' => SyncLogTable::ENTITY_TYPE_RESOURCE,
                'entity_id' => $resource->get('id'),
                'op' => SyncLogTable::OP_DELETE,
            ],
            $this->journal()
        );
    }

    public function testAPermissionChangeIsRecordedOnTheAffectedResource(): void
    {
        $resource = ResourceFactory::make()->persist();
        $user = UserFactory::make()->persist();

        PermissionFactory::make()
            ->setField('aco', PermissionsTable::RESOURCE_ACO)
            ->setField('aco_foreign_key', $resource->get('id'))
            ->setField('aro', PermissionsTable::USER_ARO)
            ->setField('aro_foreign_key', $user->get('id'))
            ->setField('type', 1)
            ->persist();

        // the permission itself is not an entity the client mirrors; what changed for it is the resource visibility
        $journal = $this->journal();
        $resourceEvents = array_filter(
            $journal,
            fn (array $row): bool => $row['entity_type'] === SyncLogTable::ENTITY_TYPE_RESOURCE
                && $row['entity_id'] === $resource->get('id')
        );

        $this->assertGreaterThanOrEqual(2, count($resourceEvents), 'expected the create and the permission change');
        $this->assertEmpty(array_filter($journal, fn (array $row): bool => $row['entity_type'] === 'permission'));
    }

    public function testAGroupMembershipChangeIsRecordedOnEveryResourceSharedWithTheGroup(): void
    {
        [$sharedResource, $unrelatedResource] = ResourceFactory::make(2)->persist();
        $group = GroupFactory::make()->persist();
        $user = UserFactory::make()->persist();

        PermissionFactory::make()
            ->setField('aco', PermissionsTable::RESOURCE_ACO)
            ->setField('aco_foreign_key', $sharedResource->get('id'))
            ->setField('aro', PermissionsTable::GROUP_ARO)
            ->setField('aro_foreign_key', $group->get('id'))
            ->setField('type', 1)
            ->persist();

        $groupsUsers = TableRegistry::getTableLocator()->get('GroupsUsers');
        $groupsUsers->saveOrFail($groupsUsers->newEntity(
            [
                'group_id' => $group->get('id'),
                'user_id' => $user->get('id'),
                'is_admin' => false,
            ],
            ['accessibleFields' => ['group_id' => true, 'user_id' => true, 'is_admin' => true]]
        ));

        $journal = $this->journal();
        $resourceIds = array_column(
            array_filter($journal, fn (array $r): bool => $r['entity_type'] === SyncLogTable::ENTITY_TYPE_RESOURCE),
            'entity_id'
        );

        $this->assertContains($sharedResource->get('id'), $resourceIds);
        // the membership says nothing about a resource the group has no permission on
        $this->assertSame(
            1,
            count(array_keys($resourceIds, $unrelatedResource->get('id'), true)),
            'the unrelated resource should only carry its own creation event'
        );
    }
}
