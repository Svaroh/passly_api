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
 * @since         5.14.1
 */
namespace App\Test\TestCase\Middleware;

use App\Test\Lib\AppIntegrationTestCase;

/**
 * @covers \App\Middleware\CsrfProtectionMiddleware
 */
class CsrfCookieHttpIntegrationTest extends AppIntegrationTestCase
{
    public function testCsrfProtectionMiddleware_Http_ResponseCookieIsNotSecure(): void
    {
        // Force the middleware to create a fresh csrfToken cookie in the response.
        $this->disableCsrfToken();

        // Requests run over plain HTTP by default (disabled in setup method)
        $this->getJson('/healthcheck/status.json');

        $this->assertResponseSuccess();
        $csrfCookie = $this->_response->getCookieCollection()->get('csrfToken');
        $this->assertFalse($csrfCookie->isSecure(), 'CSRF cookie must not be Secure over HTTP');
    }

    public function testCsrfProtectionMiddleware_Https_ResponseCookieIsSecure(): void
    {
        $this->disableCsrfToken();
        $this->configRequest(['environment' => ['HTTPS' => 'on']]);

        $this->getJson('/healthcheck/status.json');

        $this->assertResponseSuccess();
        $csrfCookie = $this->_response->getCookieCollection()->get('csrfToken');
        $this->assertTrue($csrfCookie->isSecure(), 'CSRF cookie must remain Secure over HTTPS');
    }
}
