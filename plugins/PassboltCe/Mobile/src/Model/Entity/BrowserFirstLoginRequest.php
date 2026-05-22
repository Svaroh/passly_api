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
 */

namespace Passbolt\Mobile\Model\Entity;

use Cake\ORM\Entity;

/**
 * Browser first-login pairing request.
 *
 * This relay intentionally stores no private key and no passphrase. It only
 * carries the short-lived GPGAuth login challenge and one-time login response.
 */
class BrowserFirstLoginRequest extends Entity
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCOUNT_SELECTED = 'account_selected';
    public const STATUS_CHALLENGE_READY = 'challenge_ready';
    public const STATUS_RESPONSE_READY = 'response_ready';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCOUNT_SELECTED,
        self::STATUS_CHALLENGE_READY,
        self::STATUS_RESPONSE_READY,
        self::STATUS_COMPLETE,
        self::STATUS_CANCELLED,
    ];

    protected array $_accessible = [
        'id' => false,
        'secret_hash' => false,
        'status' => false,
        'user_id' => false,
        'user_key_fingerprint' => false,
        'encrypted_user_auth_token' => false,
        'user_token_result' => false,
        'expires' => false,
        'created' => false,
        'modified' => false,
    ];

    protected array $_hidden = [
        'secret_hash',
    ];
}
