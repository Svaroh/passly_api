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
use Passbolt\Sync\Service\SyncBootstrapService;
use Passbolt\Sync\Service\SyncMetaService;

class SyncBootstrapController extends AppController
{
    /**
     * @return void
     */
    public function get(): void
    {
        $this->assertJson();

        $page = $this->assertPage();
        $limit = $this->assertLimit();
        $cursor = $this->getCursor();

        $bootstrap = (new SyncBootstrapService())->get(
            $this->User->id(),
            $this->User->role(),
            $page,
            $limit,
            $cursor,
        );

        $this->success(__('The operation was successful.'), $bootstrap->toArray());

        /** @var array<string, mixed> $header */
        $header = $this->viewBuilder()->getVar('header') ?? [];
        $header['pagination'] = [
            'count' => $bootstrap->getTotalCount(),
            'page' => $page,
            'limit' => $limit,
        ];
        $this->set('header', $header);
    }

    /**
     * @return int
     * @throws \Cake\Http\Exception\BadRequestException If page is not a positive integer.
     */
    private function assertPage(): int
    {
        $page = $this->request->getQuery('page', 1);

        if (!is_numeric($page) || (int)$page < 1 || (string)(int)$page !== (string)$page) {
            throw new BadRequestException(__('The page should be a positive integer.'));
        }

        return (int)$page;
    }

    /**
     * @return int
     * @throws \Cake\Http\Exception\BadRequestException If limit is out of range.
     */
    private function assertLimit(): int
    {
        $limit = $this->request->getQuery('limit', SyncMetaService::MAX_PAGE_SIZE);

        if (!is_numeric($limit) || (int)$limit < 1 || (int)$limit > SyncMetaService::MAX_PAGE_SIZE) {
            throw new BadRequestException(
                __('The limit should be an integer between 1 and {0}.', SyncMetaService::MAX_PAGE_SIZE),
            );
        }

        return (int)$limit;
    }

    /**
     * @return int|null
     * @throws \Cake\Http\Exception\BadRequestException If cursor is invalid.
     */
    private function getCursor(): ?int
    {
        $cursor = $this->request->getQuery('cursor');
        if ($cursor === null || $cursor === '') {
            return null;
        }

        if (!is_numeric($cursor) || (int)$cursor < 0 || (string)(int)$cursor !== (string)$cursor) {
            throw new BadRequestException(__('The cursor should be a positive integer.'));
        }

        return (int)$cursor;
    }
}
