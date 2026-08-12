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

namespace Passbolt\Sync;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Passbolt\Sync\Events\SyncLogModelListener;

/**
 * Change journal and delta endpoints for the sync/1 protocol.
 *
 * @see docs/sync-protocol.md
 */
class SyncPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);

        $app->getEventManager()->on(new SyncLogModelListener());
    }
}
