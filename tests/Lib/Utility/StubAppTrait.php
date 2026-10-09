<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         5.15.0
 */
namespace App\Test\Lib\Utility;

use Cake\Core\PluginApplicationInterface;
use Cake\Event\EventManager;

trait StubAppTrait
{
    /**
     * Returns a stub PluginApplicationInterface suitable for passing to
     * `Plugin::bootstrap()` in a unit-test setUp. `getEventManager()` is wired
     * to the global EventManager so any Model.initialize listener the plugin
     * registers actually fires on subsequent Table instantiations.
     *
     * @return \Cake\Core\PluginApplicationInterface
     */
    protected function stubApp(): PluginApplicationInterface
    {
        $app = $this->createMock(PluginApplicationInterface::class);
        $app->method('getEventManager')->willReturn(EventManager::instance());

        return $app;
    }
}
