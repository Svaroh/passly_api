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

class V51235AddBrowserFirstLoginRequests extends AbstractMigration
{
    /**
     * Up
     *
     * @return void
     */
    public function up()
    {
        $this->table('browser_first_login_requests', [
            'id' => false,
            'primary_key' => ['id'],
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('id', 'char', [
                'encoding' => 'ascii',
                'collation' => 'ascii_general_ci',
                'default' => null,
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('secret_hash', 'char', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'encoding' => 'ascii',
                'collation' => 'ascii_general_ci',
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('user_id', 'char', [
                'encoding' => 'ascii',
                'collation' => 'ascii_general_ci',
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('user_key_fingerprint', 'char', [
                'default' => null,
                'limit' => 40,
                'null' => true,
            ])
            ->addColumn('expires', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex('id', ['unique' => true])
            ->addIndex('secret_hash')
            ->addIndex('expires')
            ->create();
    }
}
// @codingStandardsIgnoreEnd
