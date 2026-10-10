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
 * @since         3.6.0
 */
namespace Passbolt\AccountRecovery;

use App\Service\Setup\RecoverCompleteServiceInterface;
use App\Service\Setup\SetupCompleteServiceInterface;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Passbolt\AccountRecovery\Event\ContainAccountRecoveryUserSettings;
use Passbolt\AccountRecovery\Event\ContainPendingAccountRecoveryRequest;
use Passbolt\AccountRecovery\Event\DeleteAccountRecoveryInfoOnUserDelete;
use Passbolt\AccountRecovery\Event\Metadata\MetadataKeysBuildRulesListener;
use Passbolt\AccountRecovery\Event\UsersModelInitializeEventListener;
use Passbolt\AccountRecovery\Notification\AccountRecoveryEmailRedactorPool;
use Passbolt\AccountRecovery\Notification\AccountRecoveryNotificationSettingsDefinition;
use Passbolt\AccountRecovery\Service\Setup\AccountRecoveryRecoverCompleteService;
use Passbolt\AccountRecovery\Service\Setup\AccountRecoverySetupCompleteService;
use Passbolt\AccountRecovery\Service\Setup\RecoverStartAccountRecoveryInfoService;
use Passbolt\AccountRecovery\Service\Setup\SetupStartAccountRecoveryInfoService;
use Passbolt\AccountRecovery\ServiceProvider\AccountRecoveryOrganizationPolicyServiceProvider;

class AccountRecoveryPlugin extends BasePlugin
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
        $container->addServiceProvider(new AccountRecoveryOrganizationPolicyServiceProvider());
        $container->add(SetupStartAccountRecoveryInfoService::class);
        $container->add(RecoverStartAccountRecoveryInfoService::class);
        $container
            ->extend(RecoverCompleteServiceInterface::class)
            ->setConcrete(AccountRecoveryRecoverCompleteService::class);
        $container
            ->extend(SetupCompleteServiceInterface::class)
            ->setConcrete(AccountRecoverySetupCompleteService::class);
    }

    /**
     * Register Account Recovery related listeners.
     *
     * @param \Cake\Core\PluginApplicationInterface $app App
     * @return void
     */
    public function registerListeners(PluginApplicationInterface $app): void
    {
        $app->getEventManager()
            ->on(new UsersModelInitializeEventListener())
            ->on(new AccountRecoveryEmailRedactorPool())
            ->on(new AccountRecoveryNotificationSettingsDefinition())
            ->on(new ContainAccountRecoveryUserSettings())
            ->on(new ContainPendingAccountRecoveryRequest())
            ->on(new DeleteAccountRecoveryInfoOnUserDelete())
            ->on(new MetadataKeysBuildRulesListener());
    }
}
