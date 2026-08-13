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

use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->plugin('Passbolt/Sync', ['path' => '/sync'], function (RouteBuilder $routes): void {
    $routes->setExtensions(['json']);

    $routes->connect('/meta', ['controller' => 'SyncMeta', 'action' => 'get'])
        ->setMethods(['GET']);

    $routes->connect('/changes', ['controller' => 'SyncChanges', 'action' => 'get'])
        ->setMethods(['GET']);
});
