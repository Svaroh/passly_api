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
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Prunes change journal rows that are older than the retention window.
 *
 * Retention only limits how far back a client may resume a delta from. A client whose cursor falls off the end is
 * told to bootstrap again; it never loses its local data over this.
 */
class SyncLogRetentionService
{
    use LocatorAwareTrait;

    public const DEFAULT_RETENTION_DAYS = 90;

    /**
     * @return int Number of pruned rows.
     */
    public function prune(): int
    {
        return $this->fetchTable('Passbolt/Sync.SyncLog')
            ->deleteAll(['created <' => $this->threshold()]);
    }

    /**
     * @return \Cake\I18n\DateTime
     */
    public function threshold(): DateTime
    {
        return DateTime::now()->subDays($this->retentionDays());
    }

    /**
     * @return int
     */
    public function retentionDays(): int
    {
        $configured = Configure::read('passbolt.plugins.sync.retentionDays');

        return is_numeric($configured) ? (int)$configured : self::DEFAULT_RETENTION_DAYS;
    }
}
