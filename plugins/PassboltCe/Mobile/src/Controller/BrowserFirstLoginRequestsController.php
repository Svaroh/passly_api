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
 */

namespace Passbolt\Mobile\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Passbolt\Mobile\Service\BrowserFirstLogin\BrowserFirstLoginRequestService;

class BrowserFirstLoginRequestsController extends AppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $this->Authentication->allowUnauthenticated([
            'create',
            'view',
            'setAccount',
            'setLoginResponse',
            'complete',
        ]);

        parent::beforeFilter($event);
    }

    /**
     * Create a first-login pairing request.
     *
     * @return void
     */
    public function create(): void
    {
        $service = new BrowserFirstLoginRequestService();
        $this->success(__('The operation was successful.'), $service->create());
    }

    /**
     * View a first-login pairing request.
     *
     * @param string $id Request id.
     * @return void
     */
    public function view(string $id): void
    {
        $service = new BrowserFirstLoginRequestService();
        $request = $service->getAuthorized($id, (string)$this->request->getData('secret'));
        $this->success(__('The operation was successful.'), $request);
    }

    /**
     * Set the Android-selected account.
     *
     * @param string $id Request id.
     * @return void
     */
    public function setAccount(string $id): void
    {
        $service = new BrowserFirstLoginRequestService();
        $request = $service->setAccount($id, $this->request->getData());
        $this->success(__('The operation was successful.'), $request);
    }

    /**
     * Set the Android-encrypted private-key payload.
     *
     * @param string $id Request id.
     * @return void
     */
    public function setLoginResponse(string $id): void
    {
        $service = new BrowserFirstLoginRequestService();
        $request = $service->setResponse($id, $this->request->getData());
        $this->success(__('The operation was successful.'), $request);
    }

    /**
     * Mark the first-login pairing request as complete.
     *
     * @param string $id Request id.
     * @return void
     */
    public function complete(string $id): void
    {
        $service = new BrowserFirstLoginRequestService();
        $request = $service->complete($id, $this->request->getData());
        $this->success(__('The operation was successful.'), $request);
    }
}
