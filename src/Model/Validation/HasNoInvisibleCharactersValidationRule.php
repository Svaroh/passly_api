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

namespace App\Model\Validation;

class HasNoInvisibleCharactersValidationRule extends PassboltValidationRule
{
    // (?!\x{20})\p{Zs} matches every Space_Separator except ASCII space — future-proof
    // against new Zs additions rather than an explicit NBSP/ideographic-space list.
    private const INVISIBLE_CHARACTERS_REGEX =
        '/[\p{Cc}\p{Cf}\p{Co}\p{Zl}\p{Zp}]|[\x{FE00}-\x{FE0F}]|(?!\x{20})\p{Zs}/u';

    /**
     * @inheritDoc
     */
    public function defaultErrorMessage(mixed $value, mixed $context): string
    {
        return __('The string should not contain invisible characters.');
    }

    /**
     * @inheritDoc
     */
    public function rule(mixed $value, mixed $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return !preg_match(self::INVISIBLE_CHARACTERS_REGEX, $value);
    }
}
