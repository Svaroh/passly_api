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
use Passbolt\Sync\Service\SyncChangesService;
use Passbolt\Sync\Service\SyncMetaService;

class SyncChangesController extends AppController
{
    /**
     * @return void
     */
    public function get(): void
    {
        $this->assertJson();

        $changes = (new SyncChangesService())->get(
            $this->User->id(),
            $this->assertSince(),
            $this->assertLimit()
        );

        $this->success(__('The operation was successful.'), $changes->toArray());
    }

    /**
     * @return int
     * @throws \Cake\Http\Exception\BadRequestException If the cursor is not a non negative integer.
     */
    private function assertSince(): int
    {
        $since = $this->request->getQuery('since', 0);

        if (!is_numeric($since) || (int)$since < 0 || (string)(int)$since !== (string)$since) {
            throw new BadRequestException(__('The cursor should be a positive integer.'));
        }

        return (int)$since;
    }

    /**
     * @return int
     * @throws \Cake\Http\Exception\BadRequestException If the limit is out of range.
     */
    private function assertLimit(): int
    {
        $limit = $this->request->getQuery('limit', SyncMetaService::MAX_PAGE_SIZE);

        if (!is_numeric($limit) || (int)$limit < 1 || (int)$limit > SyncMetaService::MAX_PAGE_SIZE) {
            throw new BadRequestException(
                __('The limit should be an integer between 1 and {0}.', SyncMetaService::MAX_PAGE_SIZE)
            );
        }

        return (int)$limit;
    }
}
