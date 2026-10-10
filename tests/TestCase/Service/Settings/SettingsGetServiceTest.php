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
namespace App\Test\TestCase\Service\Settings;

use App\Model\Entity\Role;
use App\Service\Settings\SettingsGetService;
use App\Test\Lib\AppTestCase;
use Cake\Core\Configure;

class SettingsGetServiceTest extends AppTestCase
{
    public function testSettingsGetService_getSettingsAsUser(): void
    {
        $service = new SettingsGetService(Role::USER);

        $version = '5.14';
        $name = 'Test Settings';
        $publicPath = 'storage-public';
        Configure::write('passbolt.version', $version);
        Configure::write('passbolt.name', $name);
        Configure::write('debug', true);
        Configure::write('ImageStorage.publicPath', $publicPath);
        $plugins = array_keys(Configure::read('passbolt.plugins'));
        foreach ($plugins as $plugin) {
            $this->enableFeaturePlugin(ucfirst($plugin));
        }

        $settings = $service->getSettings()->toArray();
        $this->assertSame($version, $settings['app']['version']['number']);
        $this->assertSame($name, $settings['app']['version']['name']);
        $this->assertSame(1, $settings['app']['debug']);
        $this->assertSame($publicPath, $settings['app']['image_storage']['public_path']);
        foreach ($plugins as $plugin) {
            $this->assertTrue($settings['passbolt']['plugins'][$plugin]['enabled']);
        }
    }

    public function testSettingsGetService_getSettingsAsGuest(): void
    {
        $service = new SettingsGetService(Role::GUEST);

        $version = '5.14';
        $name = 'Test Settings';
        $publicPath = 'storage-public';
        Configure::write('passbolt.version', $version);
        Configure::write('passbolt.name', $name);
        Configure::write('debug', true);
        Configure::write('ImageStorage.publicPath', $publicPath);
        $plugins = array_keys(Configure::read('passbolt.plugins'));
        foreach ($plugins as $plugin) {
            $this->enableFeaturePlugin(ucfirst($plugin));
        }

        $settings = $service->getSettings()->toArray();
        $this->assertFalse(isset($settings['app']['version']));
        $this->assertFalse(isset($settings['app']['version']['number']));
        $this->assertFalse(isset($settings['app']['version']['name']));
        $this->assertFalse(isset($settings['app']['debug']));
        $this->assertFalse(isset($settings['app']['image_storage']['public_path']));
        $this->assertFalse(isset($settings['passbolt']['plugins']['export']['enabled']));
        $this->assertFalse(isset($settings['passbolt']['plugins']['disableUser']['enabled']));
        $this->assertTrue(isset($settings['passbolt']['plugins']['accountRecoveryRequestHelp']['enabled']));
        $this->assertTrue(isset($settings['passbolt']['plugins']['safari']['enabled']));
    }
}
