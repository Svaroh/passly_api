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
 * @since         4.1.0
 */
namespace Passbolt\Rbacs;

use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Passbolt\Rbacs\Event\ClearRbacsOnRoleDeleteListener;
use Passbolt\Rbacs\Event\CreateRbacsOnRoleCreateListener;
use Passbolt\Rbacs\Event\RbacsModelInitializeEventListener;
use Passbolt\Rbacs\Service\ActionAccessControl\RbacsRoleActionAccessControlService;
use Passbolt\Rbacs\Service\ActionAccessControl\RoleActionAccessControlServiceInterface;

class RbacsPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);
        $this->registerListeners($app);
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container
            ->extend(RoleActionAccessControlServiceInterface::class)
            ->setConcrete(RbacsRoleActionAccessControlService::class);
    }

    /**
     * Register Rbacs related listeners.
     *
     * @param \Cake\Core\PluginApplicationInterface $app App
     * @return void
     */
    public function registerListeners(PluginApplicationInterface $app): void
    {
        $eventManager = $app->getEventManager();

        $eventManager
            ->on(new RbacsModelInitializeEventListener())
            ->on(new CreateRbacsOnRoleCreateListener())
            ->on(new ClearRbacsOnRoleDeleteListener());
    }
}
