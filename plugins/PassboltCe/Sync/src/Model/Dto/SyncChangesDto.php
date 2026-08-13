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
 * One page of a delta.
 */
class SyncChangesDto
{
    /**
     * @param int $cursor Highest applied sequence number, to be sent back as `since` next time.
     * @param bool $hasMore Whether more settled events are waiting.
     * @param array<string, array<mixed>> $upserts Entities the caller can see, keyed by collection.
     * @param array<string, array<string>> $deletions Ids the caller can no longer see, keyed by collection.
     */
    public function __construct(
        private readonly int $cursor,
        private readonly bool $hasMore,
        private readonly array $upserts,
        private readonly array $deletions,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cursor' => $this->cursor,
            'has_more' => $this->hasMore,
            'upserts' => (object)$this->upserts,
            'deletions' => (object)$this->deletions,
        ];
    }
}
