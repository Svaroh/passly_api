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

use Cake\Log\Log;
use Migrations\AbstractMigration;

class V5150CreateSessionsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        // Some PRO customers already loaded config/schema/sessions.sql manually; skip in that case.
        if ($this->hasTable('sessions')) {
            Log::info(__('The table `sessions` already exists.'));

            return;
        }

        $this
            ->table('sessions', [
                'id' => false,
                'primary_key' => ['id'],
                'encoding' => 'ascii',
                'collation' => 'ascii_bin',
            ])
            ->addColumn('id', 'char', [
                'limit' => 40,
                'null' => false,
                'encoding' => 'ascii',
                'collation' => 'ascii_bin',
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('data', 'blob', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('expires', 'integer', [
                'default' => null,
                'signed' => false,
                'null' => true,
            ])
            ->create();
    }
}
