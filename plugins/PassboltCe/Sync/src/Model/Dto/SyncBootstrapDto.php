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

namespace Passbolt\Sync\Model\Dto;

/**
 * Initial snapshot or slice returned by the bootstrap endpoint.
 */
class SyncBootstrapDto
{
    /**
     * @param int $cursor Watermark sequence number from which subsequent delta synchronisation continues.
     * @param string $epoch Server identity token.
     * @param int $totalCount Total count of resources across all pages.
     * @param array<string, array<mixed>> $upserts Snapshot entities keyed by collection.
     */
    public function __construct(
        private readonly int $cursor,
        private readonly string $epoch,
        private readonly int $totalCount,
        private readonly array $upserts,
    ) {
    }

    /**
     * @return int
     */
    public function getCursor(): int
    {
        return $this->cursor;
    }

    /**
     * @return string
     */
    public function getEpoch(): string
    {
        return $this->epoch;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cursor' => $this->cursor,
            'epoch' => $this->epoch,
            'upserts' => [
                'resources' => $this->upserts['resources'] ?? [],
                'secrets' => $this->upserts['secrets'] ?? [],
                'folders' => $this->upserts['folders'] ?? [],
                'users' => $this->upserts['users'] ?? [],
                'groups' => $this->upserts['groups'] ?? [],
                'resource_types' => $this->upserts['resource_types'] ?? [],
                'metadata_keys' => $this->upserts['metadata_keys'] ?? [],
            ],
        ];
    }
}
