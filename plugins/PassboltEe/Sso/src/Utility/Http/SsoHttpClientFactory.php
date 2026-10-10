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

namespace Passbolt\Sso\Utility\Http;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Builds the Guzzle client used by the SSRF-exposed SSO providers (OAuth2 / ADFS / PingOne):
 * SSL verify configuration plus the SSRF egress guard.
 */
class SsoHttpClientFactory
{
    public const CONFIG_SSL_VERIFY = 'passbolt.security.sso.sslVerify';
    public const CONFIG_SSL_CAFILE = 'passbolt.security.sso.sslCafile';

    /**
     * Build the guarded HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    public static function create(): Client
    {
        $stack = HandlerStack::create();
        // Placed AFTER allow_redirects so it re-validates the initial request and every redirect hop.
        $stack->after('allow_redirects', new SsoEgressGuardMiddleware(), 'sso_egress_guard');

        return new Client([
            'handler' => $stack,
            'verify' => self::resolveVerify(),
        ]);
    }

    /**
     * Resolve the Guzzle `verify` option from the SSO SSL configuration.
     *
     * @see https://docs.guzzlephp.org/en/stable/request-options.html#verify
     * @return string|bool `true` for default verification, `false` to disable, or a CA file path.
     * @throws \Cake\Http\Exception\BadRequestException When a custom CA file is configured but invalid.
     */
    private static function resolveVerify(): bool|string
    {
        $sslVerify = (bool)Configure::read(self::CONFIG_SSL_VERIFY, true);
        $sslCafile = Configure::read(self::CONFIG_SSL_CAFILE);

        if ($sslVerify && $sslCafile === null) {
            return true;
        }
        if (!$sslVerify) {
            return false;
        }
        if (!is_string($sslCafile)) {
            throw new BadRequestException(__('Invalid value provided in `passbolt.security.sso.sslCafile` config'));
        }
        if (!file_exists($sslCafile)) {
            throw new BadRequestException(__('Provided root CA file does not exist'));
        }

        return $sslCafile;
    }
}
