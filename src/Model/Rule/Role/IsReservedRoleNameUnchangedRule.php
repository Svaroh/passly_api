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
 * @since         5.14.0
 */

namespace App\Model\Rule\Role;

use App\Model\Entity\Role;
use App\Model\Table\RolesTable;

class IsReservedRoleNameUnchangedRule
{
    /**
     * @param \App\Model\Entity\Role $role The role being updated.
     * @param array $options Options passed to the check.
     * @return bool
     */
    public function __invoke(Role $role, array $options): bool
    {
        if (!$role->isDirty('name')) {
            return true;
        }

        $originalName = $role->getOriginal('name');

        return !in_array($originalName, RolesTable::RESERVED_ROLE_NAMES);
    }
}
