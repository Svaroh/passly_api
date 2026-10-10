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
 * @since         5.13.0
 */
namespace App\Test\Lib\Database\Driver;

use Cake\Database\Driver\Mysql;

/**
 * Real, instantiable driver whose class name is not a default supported driver,
 * standing in for a custom driver shipped outside of Passbolt core. This is needed by the database health check tests
 */
class CustomDatabaseDriverStub extends Mysql
{
}
