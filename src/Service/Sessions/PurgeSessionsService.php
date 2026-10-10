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

namespace App\Service\Sessions;

use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;

class PurgeSessionsService
{
    use LocatorAwareTrait;

    /**
     * Dry run of the purge
     *
     * @param int $retentionInDays retention in days
     * @param int $limit Maximum number of rows to purge.
     * @return int
     */
    public function dryRun(int $retentionInDays, int $limit): int
    {
        $ids = $this->getSessionsToPurge($retentionInDays, $limit)->toArray();

        return count($ids);
    }

    /**
     * Purge sessions.
     *
     * @param int $retentionInDays retention in days
     * @param int $limit Maximum number of rows to purge.
     * @return int
     */
    public function purge(int $retentionInDays, int $limit): int
    {
        $sessionIds = $this->getSessionsToPurge($retentionInDays, $limit)->all()->extract('id')->toArray();
        if (count($sessionIds) === 0) {
            return 0;
        }

        $sessionsTable = $this->fetchTable('Sessions');

        return $sessionsTable->deleteAll([
            'id IN' => $sessionIds,
        ]);
    }

    /**
     * @param int $retentionInDays retention in days
     * @param int $limit Maximum number of rows to purge
     * @return \Cake\ORM\Query\SelectQuery
     */
    private function getSessionsToPurge(int $retentionInDays, int $limit): SelectQuery
    {
        $modifiedBefore = FrozenTime::now()->subHours($retentionInDays * 24); // Multiplying days for 24 hours
        $sessionsTable = $this->fetchTable('Sessions');

        $query = $sessionsTable
            ->find()
            ->select(['id'])
            ->orderByAsc('modified')
            ->limit($limit);

        if ($retentionInDays > 0) {
            $query->where(['modified <' => $modifiedBefore]);
        }

        return $query;
    }
}
