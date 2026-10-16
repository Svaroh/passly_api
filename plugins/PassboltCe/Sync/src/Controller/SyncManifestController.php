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

namespace Passbolt\Sync\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Passbolt\Sync\Service\SyncManifestService;

class SyncManifestController extends AppController
{
    /**
     * @return void
     */
    public function get(): void
    {
        $this->assertJson();

        $type = $this->assertType();
        $manifest = (new SyncManifestService())->get($this->User->id(), $type);

        $this->success(__('The operation was successful.'), $manifest);
    }

    /**
     * @return string
     * @throws \Cake\Http\Exception\BadRequestException If type is unsupported.
     */
    private function assertType(): string
    {
        $type = $this->request->getQuery('type', 'resources');

        if (!in_array($type, SyncManifestService::SUPPORTED_TYPES, true)) {
            throw new BadRequestException(__('The entity type is not supported.'));
        }

        return $type;
    }
}
