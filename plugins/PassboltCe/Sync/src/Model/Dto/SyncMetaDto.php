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
 * What a client needs to know before it starts synchronising.
 */
class SyncMetaDto
{
    /**
     * @param string $protocol Wire protocol identifier, see docs/sync-protocol.md.
     * @param string $epoch Opaque server identity; a change forces clients to bootstrap again.
     * @param int $maxPageSize Largest page a client may ask for.
     * @param int $retentionDays How far back a delta may be resumed from.
     */
    public function __construct(
        private readonly string $protocol,
        private readonly string $epoch,
        private readonly int $maxPageSize,
        private readonly int $retentionDays,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'protocol' => $this->protocol,
            'epoch' => $this->epoch,
            'max_page_size' => $this->maxPageSize,
            'retention_days' => $this->retentionDays,
        ];
    }
}
