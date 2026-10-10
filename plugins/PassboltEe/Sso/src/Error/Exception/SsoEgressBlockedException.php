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

namespace Passbolt\Sso\Error\Exception;

use Throwable;

/**
 * Thrown by the egress guard when the server refuses to connect to a blocked provider address.
 */
class SsoEgressBlockedException extends OAuth2Exception
{
    /**
     * @param string $errorDescription Error description.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(string $errorDescription, ?Throwable $previous = null)
    {
        parent::__construct('egress_blocked', $errorDescription, 400, $previous);
    }

    /**
     * The egress guard middleware already logs the detailed block reason, so no need to log again.
     *
     * @return void
     */
    protected function logError(): void
    {
    }
}
