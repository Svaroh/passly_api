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

class V5150AddSessionsModifiedIndex extends AbstractMigration
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
        // Some users can have already created the index on their own; skip in that case.
        $table = $this->table('sessions');
        if ($table->hasIndex('modified')) {
            Log::info(__('The index for `modified` column in sessions table already exists.'));

            return;
        }

        $this
            ->table('sessions')
            ->addIndex('modified')
            ->update();
    }
}
