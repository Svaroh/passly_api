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

namespace Passbolt\Sso\Test\TestCase\Utility\Http;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\TestSuite\TestCase;
use GuzzleHttp\Client;
use Passbolt\Sso\Utility\Http\SsoHttpClientFactory;

/**
 * @covers \Passbolt\Sso\Utility\Http\SsoHttpClientFactory
 */
class SsoHttpClientFactoryTest extends TestCase
{
    public function testSsoHttpClientFactory_Success_ReturnsClientWithDefaultVerify(): void
    {
        Configure::write('passbolt.security.sso.sslVerify', true);
        Configure::write('passbolt.security.sso.sslCafile', null);

        $this->assertInstanceOf(Client::class, SsoHttpClientFactory::create());
    }

    public function testSsoHttpClientFactory_Success_VerifyDisabled(): void
    {
        Configure::write('passbolt.security.sso.sslVerify', false);
        Configure::write('passbolt.security.sso.sslCafile', null);

        $this->assertInstanceOf(Client::class, SsoHttpClientFactory::create());
    }

    public function testSsoHttpClientFactory_Error_MissingCafile(): void
    {
        Configure::write('passbolt.security.sso.sslVerify', true);
        Configure::write('passbolt.security.sso.sslCafile', '/does/not/exist.crt');

        $this->expectException(BadRequestException::class);
        SsoHttpClientFactory::create();
    }
}
