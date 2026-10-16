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

use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Folders\Model\Behavior\FolderizableBehavior;
use Passbolt\Metadata\Service\MetadataKeysIndexService;
use Passbolt\Metadata\Service\MetadataResourcesRenderService;
use Passbolt\Sync\Model\Dto\SyncBootstrapDto;
use Passbolt\Sync\Utility\SyncSecretAccessLoggerTrait;

/**
 * Produces a complete bootstrap slice for initial synchronisation.
 */
class SyncBootstrapService
{
    use LocatorAwareTrait;
    use SyncSecretAccessLoggerTrait;

    /**
     * @param string $userId Caller identifier.
     * @param string $userRole Caller role name.
     * @param int $page Current page number.
     * @param int $limit Maximum number of resources per page.
     * @param int|null $pinnedCursor Pinned cursor for pages > 1.
     * @return \Passbolt\Sync\Model\Dto\SyncBootstrapDto
     */
    public function get(
        string $userId,
        string $userRole,
        int $page,
        int $limit,
        ?int $pinnedCursor = null,
    ): SyncBootstrapDto {
        $cursor = $this->resolveCursor($pinnedCursor);
        $epoch = (new SyncMetaService())->epoch();

        [$resources, $totalCount] = $this->findResourcesPage($userId, $page, $limit);
        $secrets = $this->findSecretsForResources($userId, $resources);

        $upserts = [
            'resources' => $resources,
            'secrets' => $secrets,
            'folders' => $page === 1 ? $this->findFolders($userId) : [],
            'users' => $page === 1 ? $this->findUsers($userRole) : [],
            'groups' => $page === 1 ? $this->findGroups() : [],
            'resource_types' => $page === 1 ? $this->findResourceTypes() : [],
            'metadata_keys' => $page === 1 ? $this->findMetadataKeys($userId) : [],
        ];

        return new SyncBootstrapDto($cursor, $epoch, $totalCount, $upserts);
    }

    /**
     * @param int|null $pinnedCursor Pinned cursor from client or null to read current watermark.
     * @return int
     */
    private function resolveCursor(?int $pinnedCursor): int
    {
        if ($pinnedCursor !== null) {
            return $pinnedCursor;
        }

        /** @var \Passbolt\Sync\Model\Table\SyncLogTable $syncLog */
        $syncLog = $this->fetchTable('Passbolt/Sync.SyncLog');

        return $syncLog->findWatermark();
    }

    /**
     * @param string $userId Caller.
     * @param int $page Page index.
     * @param int $limit Page size.
     * @return array{0: array<array<string, mixed>>, 1: int} List of resources and total count.
     */
    private function findResourcesPage(string $userId, int $page, int $limit): array
    {
        /** @var \App\Model\Table\ResourcesTable $resourcesTable */
        $resourcesTable = $this->fetchTable('Resources');

        $contain = [
            'permission' => true,
            'permissions' => true,
            'favorite' => true,
        ];
        if (Configure::read('passbolt.plugins.tags')) {
            $contain['tag'] = true;
        }

        $query = $resourcesTable->findIndex($userId, ['contain' => $contain]);
        $totalCount = $query->count();

        $resources = $query
            ->orderBy(['Resources.id' => 'ASC'])
            ->limit($limit)
            ->page($page)
            ->disableHydration()
            ->toArray();

        if (Configure::read('passbolt.plugins.folders.enabled') && class_exists(FolderizableBehavior::class)) {
            $resources = array_map(
                static fn(array $resource): array => FolderizableBehavior::unsetPersonalPropertyIfNull($resource),
                $resources,
            );
        }

        if (class_exists(MetadataResourcesRenderService::class)) {
            $resources = (new MetadataResourcesRenderService())->renderResources($resources);
        }

        return [$resources, $totalCount];
    }

    /**
     * @param string $userId Caller.
     * @param array<array<string, mixed>> $resources Visible resources on this page.
     * @return array<array<string, mixed>>
     */
    private function findSecretsForResources(string $userId, array $resources): array
    {
        $resourceIds = array_column($resources, 'id');
        if (empty($resourceIds)) {
            return [];
        }

        $secrets = $this->fetchTable('Secrets')
            ->find()
            ->select(['id', 'resource_id', 'data', 'modified'])
            ->where(['user_id' => $userId, 'resource_id IN' => $resourceIds])
            ->orderBy(['resource_id' => 'ASC'])
            ->disableHydration()
            ->toArray();

        $this->logSecretAccesses($userId, $secrets);

        return $secrets;
    }

    /**
     * @param string $userId Caller.
     * @return array<array<string, mixed>>
     */
    private function findFolders(string $userId): array
    {
        if (!Configure::read('passbolt.plugins.folders.enabled')) {
            return [];
        }

        /** @var \Passbolt\Folders\Model\Table\FoldersTable $foldersTable */
        $foldersTable = $this->fetchTable('Passbolt/Folders.Folders');

        return $foldersTable
            ->findIndex($userId, [
                'contain' => [
                    'permission' => true,
                    'permissions' => true,
                ],
            ])
            ->orderBy(['Folders.id' => 'ASC'])
            ->disableHydration()
            ->toArray();
    }

    /**
     * @param string $userRole Caller role.
     * @return array<array<string, mixed>>
     */
    private function findUsers(string $userRole): array
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        return $usersTable
            ->findIndex($userRole, [
                'contain' => [
                    'profile' => true,
                    'gpgkey' => true,
                ],
            ])
            ->orderBy(['Users.id' => 'ASC'])
            ->disableHydration()
            ->toArray();
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function findGroups(): array
    {
        /** @var \App\Model\Table\GroupsTable $groupsTable */
        $groupsTable = $this->fetchTable('Groups');

        return $groupsTable
            ->findIndex([
                'contain' => [
                    'groups_users' => true,
                ],
            ])
            ->orderBy(['Groups.id' => 'ASC'])
            ->disableHydration()
            ->toArray();
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function findResourceTypes(): array
    {
        $hasTable = $this->getTableLocator()->exists('Passbolt/ResourceTypes.ResourceTypes');
        $classExists = class_exists('Passbolt\ResourceTypes\Model\Table\ResourceTypesTable');
        if (!$hasTable && !$classExists) {
            return [];
        }

        return $this->fetchTable('Passbolt/ResourceTypes.ResourceTypes')
            ->find('notDeleted')
            ->orderBy(['id' => 'ASC'])
            ->disableHydration()
            ->toArray();
    }

    /**
     * @param string $userId Caller.
     * @return array<array<string, mixed>>
     */
    private function findMetadataKeys(string $userId): array
    {
        if (!class_exists(MetadataKeysIndexService::class)) {
            return [];
        }

        return (new MetadataKeysIndexService())
            ->get($userId, ['metadata_private_keys' => true])
            ->orderBy(['MetadataKeys.id' => 'ASC'])
            ->disableHydration()
            ->toArray();
    }
}
