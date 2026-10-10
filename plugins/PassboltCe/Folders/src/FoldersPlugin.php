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
 * @since         3.7.0
 */
namespace Passbolt\Folders;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Passbolt\EmailDigest\Utility\Digest\DigestTemplateRegistry;
use Passbolt\Folders\EventListener\GroupsUsersEventListener;
use Passbolt\Folders\EventListener\PermissionsModelInitializeEventListener;
use Passbolt\Folders\EventListener\ResourcesEventListener;
use Passbolt\Folders\EventListener\ResourcesModelInitializeEventListener;
use Passbolt\Folders\Notification\DigestTemplate\FolderChangesDigestTemplate;
use Passbolt\Folders\Notification\Email\FoldersEmailRedactorPool;
use Passbolt\Folders\Notification\NotificationSettings\FolderNotificationSettingsDefinition;

class FoldersPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);
        $this->registerListeners($app);
        // Folders email digests
        DigestTemplateRegistry::getInstance()->addTemplate(new FolderChangesDigestTemplate());
    }

    /**
     * Register Folders related listeners.
     *
     * @param \Cake\Core\PluginApplicationInterface $app App
     * @return void
     */
    public function registerListeners(PluginApplicationInterface $app): void
    {
        $app->getEventManager()
            ->on(new ResourcesEventListener()) //Add / remove folders relations when a resources is created / deleted
            ->on(new GroupsUsersEventListener()) // Add / remove folders relations when a group members list is updated
            ->on(new ResourcesModelInitializeEventListener()) // Decorate the resources table class with folder relations and folderizable behavior
            ->on(new PermissionsModelInitializeEventListener()) // Decorate the permissions table class with folder association and cleanup behavior
            ->on(new FolderNotificationSettingsDefinition())// Add email notification settings definition
            ->on(new FoldersEmailRedactorPool()); // Register email redactors
    }
}
