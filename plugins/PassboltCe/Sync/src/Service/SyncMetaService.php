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
use Passbolt\Sync\Model\Dto\SyncMetaDto;

/**
 * Describes this installation's synchronisation capabilities to a client.
 */
class SyncMetaService
{
    public const PROTOCOL = 'sync/1';

    public const MAX_PAGE_SIZE = 500;

    /**
     * @return \Passbolt\Sync\Model\Dto\SyncMetaDto
     */
    public function get(): SyncMetaDto
    {
        return new SyncMetaDto(
            self::PROTOCOL,
            $this->epoch(),
            self::MAX_PAGE_SIZE,
            (new SyncLogRetentionService())->retentionDays()
        );
    }

    /**
     * Identifies the installation a cursor belongs to.
     *
     * It is derived from the server key fingerprint and the base url, so pointing a client at a different
     * installation - or restoring one from a different backup - invalidates its cursor instead of letting it apply a
     * delta computed against someone else's journal.
     *
     * A journal that is truncated while the installation stays the same is not covered here on purpose: that case is
     * already handled by the retention check, which answers 410 when a cursor predates the oldest retained event.
     *
     * @return string
     */
    private function epoch(): string
    {
        $fingerprint = (string)Configure::read('passbolt.gpg.serverKey.fingerprint');
        $baseUrl = (string)Configure::read('App.fullBaseUrl');

        return substr(hash('sha256', $fingerprint . '|' . $baseUrl), 0, 32);
    }
}
