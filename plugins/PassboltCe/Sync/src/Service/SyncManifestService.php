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

use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;
use DateTimeInterface;

/**
 * Generates lightweight reconciliation manifests for client diffing.
 */
class SyncManifestService
{
    use LocatorAwareTrait;

    public const SUPPORTED_TYPES = ['resources'];

    /**
     * @param string $userId Caller identifier.
     * @param string $type Entity collection type to reconcile.
     * @return array<array{id: string, modified: string, secret_modified: string|null}>
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function get(string $userId, string $type = 'resources'): array
    {
        if ($type !== 'resources') {
            throw new BadRequestException(__('The entity type is not supported.'));
        }

        return $this->getResourcesManifest($userId);
    }

    /**
     * @param string $userId Caller identifier.
     * @return array<array{id: string, modified: string, secret_modified: string|null}>
     */
    private function getResourcesManifest(string $userId): array
    {
        /** @var \App\Model\Table\ResourcesTable $resourcesTable */
        $resourcesTable = $this->fetchTable('Resources');

        $rows = $resourcesTable
            ->findIndex($userId)
            ->select([
                'id' => 'Resources.id',
                'modified' => 'Resources.modified',
                'secret_modified' => 'Secrets.modified',
            ])
            ->leftJoinWith('Secrets', function (SelectQuery $q) use ($userId) {
                return $q->where(['Secrets.user_id' => $userId]);
            })
            ->orderBy(['Resources.id' => 'ASC'])
            ->disableHydration()
            ->toArray();

        $manifest = [];
        foreach ($rows as $row) {
            $modified = $this->formatDate($row['modified']);
            $secretModified = !empty($row['secret_modified'])
                ? $this->formatDate($row['secret_modified'])
                : null;

            $manifest[] = [
                'id' => (string)$row['id'],
                'modified' => $modified,
                'secret_modified' => $secretModified,
            ];
        }

        return $manifest;
    }

    /**
     * @param mixed $date Date representation.
     * @return string
     */
    private function formatDate(mixed $date): string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format(DateTimeInterface::ATOM);
        }

        return date(DateTimeInterface::ATOM, (int)strtotime((string)$date));
    }
}
