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
 * @since         2.0.0
 */

namespace App\Controller\Settings;

use App\Controller\AppController;
use App\Service\Settings\SettingsGetService;
use Cake\Event\EventInterface;

/**
 * SettingsIndexController Class
 */
class SettingsIndexController extends AppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $this->Authentication->allowUnauthenticated(['index']);

        parent::beforeFilter($event);
    }

    /**
     * Settings Index action
     *
     * @return void
     */
    public function index()
    {
        $this->assertJson();

        $role = $this->User->role();
        // Retrieve and sanity the query options.
        $whitelist = [
            'contain' => ['header'],
        ];
        $options = $this->QueryString->get($whitelist);
        $withHeader = !(isset($options['contain']['header']) && $options['contain']['header'] === false);

        $settings = (new SettingsGetService($role))->getSettings()->toArray();

        if (!$withHeader) {
            $this->set($settings);
            $this->viewBuilder()->setOption('serialize', array_keys($settings));

            return;
        }
        $this->success(__('The operation was successful.'), $settings);
    }
}
