<?php

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
 * @since         3.1.0
 */
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */

$routes->plugin('Passbolt/Mobile', ['path' => '/mobile'], function (RouteBuilder $routes): void {
    $routes->setExtensions(['json']);

    /**
     * Data transfers using 3rd party channels such as QR Codes
     */
    // start transfer
    $routes->connect('/transfers', ['prefix' => 'Transfers', 'controller' => 'TransfersCreate', 'action' => 'create'])
        ->setMethods(['POST']);

    // update transfer
    $routes
        ->connect('/transfers/{id}', [
            'prefix' => 'Transfers', 'controller' => 'TransfersUpdate', 'action' => 'update',
        ])
        ->setMethods(['POST', 'PUT'])
        ->setPass(['id']);

    // without authToken
    $routes
        ->connect('/transfers/{id}/{authToken}', [
            'prefix' => 'Transfers', 'controller' => 'TransfersUpdate', 'action' => 'updateNoSession',
        ])
        ->setMethods(['POST', 'PUT'])
        ->setPass(['id', 'authToken']);

    // view transfer status
    $routes->connect('/transfers/{id}', ['prefix' => 'Transfers', 'controller' => 'TransfersView', 'action' => 'view'])
        ->setMethods(['GET'])
        ->setPass(['id']);

    /**
     * Browser first login relay.
     *
     * The browser owns the web session cookie. Android owns the private key.
     * These endpoints relay only the short-lived GPGAuth challenge/response.
     */
    $routes->connect('/browser-first-login/requests', [
        'controller' => 'BrowserFirstLoginRequests',
        'action' => 'create',
    ])->setMethods(['POST']);

    $routes->connect('/browser-first-login/requests/{id}/view', [
        'controller' => 'BrowserFirstLoginRequests',
        'action' => 'view',
    ])->setMethods(['POST'])->setPass(['id']);

    $routes->connect('/browser-first-login/requests/{id}/account', [
        'controller' => 'BrowserFirstLoginRequests',
        'action' => 'setAccount',
    ])->setMethods(['POST'])->setPass(['id']);

    $routes->connect('/browser-first-login/requests/{id}/challenge', [
        'controller' => 'BrowserFirstLoginRequests',
        'action' => 'setChallenge',
    ])->setMethods(['POST'])->setPass(['id']);

    $routes->connect('/browser-first-login/requests/{id}/response', [
        'controller' => 'BrowserFirstLoginRequests',
        'action' => 'setResponse',
    ])->setMethods(['POST'])->setPass(['id']);

    $routes->connect('/browser-first-login/requests/{id}/complete', [
        'controller' => 'BrowserFirstLoginRequests',
        'action' => 'complete',
    ])->setMethods(['POST'])->setPass(['id']);
});
