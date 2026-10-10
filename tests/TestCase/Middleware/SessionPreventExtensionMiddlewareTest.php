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
 * @since         2.11.0
 */

namespace App\Test\TestCase\Middleware;

use App\Middleware\SessionPreventExtensionMiddleware;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Test for SessionPreventExtensionMiddleware
 */
class SessionPreventExtensionMiddlewareTest extends TestCase
{
    /**
     * Ensure the middleware stores the time a request is made while requesting controller which does not prevent the session extension.
     * ie. $_SESSION['SessionPreventExtensionMiddleware']['time'] should contain the latest accessed time.
     *
     * @return void
     */
    public function testSessionPreventExtensionMiddleware_StoreSessionAccessTimeWhenNotPreventingSessionExtension()
    {
        $request = new ServerRequest();
        $request = $request
            ->withAttribute('params', ['controller' => 'AuthLoginController', 'action' => 'loginGet'])
            ->withAttribute('identity', 'foo');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $middleware = new SessionPreventExtensionMiddleware();
        $middleware->process($request, $handler);

        // The time of the request is stored in session as new middleware session time reference
        $this->assertNotNull($request->getSession()->read('SessionPreventExtensionMiddleware.time'));
        // The Cakephp time reference is not altered by the middleware
        $this->assertNull($request->getSession()->read('Config'));
    }

    /**
     * Ensure the middleware does not extend the session while requesting /auth/is-authenticated
     * - The middleware session time reference is not altered with the current request time.
     * - The Cakephp time reference is altered with the previously stored middleware session time reference.
     *
     * @return void
     */
    public function testSessionPreventExtensionMiddleware_ReuseSessionAccessTimeWhenPreventingSessionExtension()
    {
        $request = new ServerRequest();
        $request = $request
            ->withAttribute('params', ['controller' => 'AuthIsAuthenticated', 'action' => 'isAuthenticated'])
            ->withAttribute('identity', 'foo');

        $handler = $this->createMock(RequestHandlerInterface::class);
        // Insert a fake time reference in session.
        $requestSession = $request->getSession();
        $timeReference = 1234;
        $requestSession->write(['SessionPreventExtensionMiddleware.time' => $timeReference]);

        $middleware = new SessionPreventExtensionMiddleware();
        $middleware->process($request, $handler);

        // The middleware session time reference is not altered with the current request time.
        $this->assertSame($timeReference, $request->getSession()->read('SessionPreventExtensionMiddleware.time'));
        // The Cakephp time reference is altered with the previously stored middleware session time reference.
        $this->assertSame($timeReference, $request->getSession()->read('Config.time'));
    }

    /**
     * Ensure the middleware does not write in the session while requesting /auth/is-authenticated and the user is logged-out
     * - The middleware session time reference is not altered with the current request time.
     * - The Cakephp time reference is altered with the previously stored middleware session time reference.
     *
     * @return void
     */
    public function testSessionPreventExtensionMiddleware_DoNotReuseSessionAccessTimeOnIsAuthenticatedWhileNotLoggedIn()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('params', ['controller' => 'AuthIsAuthenticated', 'action' => 'isAuthenticated']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        // Insert a fake time reference in session.
        $requestSession = $request->getSession();
        $timeReference = 1234;
        $requestSession->write(['SessionPreventExtensionMiddleware.time' => $timeReference]);

        $middleware = new SessionPreventExtensionMiddleware();
        $middleware->process($request, $handler);

        // The time of the request is untouched (although in production the session would be empty)
        $this->assertSame($timeReference, $request->getSession()->read('SessionPreventExtensionMiddleware.time'));
        // The Cakephp time reference is not altered by the middleware
        $this->assertNull($request->getSession()->read('Config'));
    }
}
