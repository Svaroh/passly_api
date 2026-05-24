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
 */
// @codingStandardsIgnoreStart
use Migrations\AbstractMigration;

class V51247AddBrowserFirstLoginEncryptedPrivateKey extends AbstractMigration
{
    /**
     * Up
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('browser_first_login_requests');

        if (!$table->hasColumn('encrypted_private_key')) {
            $table->addColumn('encrypted_private_key', 'text', [
                'default' => null,
                'null' => true,
            ]);
        }

        if ($table->hasColumn('encrypted_user_auth_token')) {
            $table->removeColumn('encrypted_user_auth_token');
        }

        if ($table->hasColumn('user_token_result')) {
            $table->removeColumn('user_token_result');
        }

        $table->update();
    }
}
// @codingStandardsIgnoreEnd
