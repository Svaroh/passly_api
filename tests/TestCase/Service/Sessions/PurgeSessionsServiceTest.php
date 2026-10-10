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

namespace App\Test\TestCase\Service\Sessions;

use App\Service\Sessions\PurgeSessionsService;
use App\Test\Factory\SessionFactory;
use App\Test\Lib\AppTestCase;
use Cake\I18n\DateTime;

class PurgeSessionsServiceTest extends AppTestCase
{
    public function testPurgeSessionsService_Success_DeletesRowsOlderThanRetention()
    {
        $service = new PurgeSessionsService();

        // 10 old sessions
        $toBeDeleted = 10;
        SessionFactory::make($toBeDeleted)->modifiedAt(DateTime::now()->subDays(10))->persist();

        // 10 fresh sessions
        $toBeIgnored = 10;
        SessionFactory::make($toBeIgnored)->modifiedNow()->persist();

        $totalDeleted = $service->purge(1, 1000);
        $this->assertSame($toBeDeleted, $totalDeleted);
        $this->assertSame($toBeIgnored, SessionFactory::count());
    }

    public function testPurgeSessionsService_Success_ZeroRetentionDeletesEveryRow()
    {
        $service = new PurgeSessionsService();

        // 10 old sessions
        $oldSessions = 10;
        SessionFactory::make($oldSessions)->modifiedAt(DateTime::now()->subDays(10))->persist();

        // 10 fresh sessions
        $freshSessions = 10;
        SessionFactory::make($freshSessions)->modifiedNow()->persist();

        $total = $oldSessions + $freshSessions;

        $totalDeleted = $service->purge(0, 1000);
        $this->assertSame($total, $totalDeleted);
        $this->assertSame(0, SessionFactory::count());
    }

    public function testPurgeSessionsService_Success_ZeroRetentionDeletesRowsCreatedAtTheSameTimeCommandStarted()
    {
        $service = new PurgeSessionsService();

        // 5 old sessions
        $oldSessions = 5;
        SessionFactory::make($oldSessions)->modifiedAt(DateTime::now()->subDays(2))->persist();

        // 5 fresh sessions
        $freshSessions = 5;
        SessionFactory::make($freshSessions)->modifiedNow()->persist();

        // 2 'future' sessions
        $futureSessions = 2;
        SessionFactory::make($futureSessions)->modifiedAt(DateTime::now()->addSeconds(10))->persist();

        $total = $oldSessions + $freshSessions + $futureSessions;

        $totalDeleted = $service->purge(0, 1000);
        $this->assertSame($total, $totalDeleted);
        $this->assertSame(0, SessionFactory::count());
    }

    public function testPurgeSessionsService_Success_ReportsZeroWhenNothingToDelete()
    {
        $service = new PurgeSessionsService();

        $totalDeleted = $service->purge(0, 1000);
        $this->assertSame(0, $totalDeleted);
        $this->assertSame(0, SessionFactory::count());
    }

    public function testPurgeSessionsService_Success_LimitCapsDeletionAndPrefersOldestRows()
    {
        $service = new PurgeSessionsService();

        $toBeCreated = 4;
        for ($i = 1; $i <= $toBeCreated; $i++) {
            SessionFactory::make()->modifiedAt(DateTime::now()->subDays($i + 1))->persist();
        }

        $limit = 2;
        $totalRemaining = $toBeCreated - $limit;

        $totalDeleted = $service->purge(1, $limit);
        $this->assertSame($limit, $totalDeleted);
        $this->assertSame($totalRemaining, SessionFactory::count());
    }

    public function testPurgeSessionsService_Success_DryRunReportsCountWithoutDeleting()
    {
        $service = new PurgeSessionsService();

        // 10 old sessions
        $oldSessions = 10;
        SessionFactory::make($oldSessions)->modifiedAt(DateTime::now()->subDays(10))->persist();

        // 10 fresh sessions
        $freshSessions = 10;
        SessionFactory::make($freshSessions)->modifiedNow()->persist();

        $total = $oldSessions + $freshSessions;

        $totalWouldBeDeleted = $service->dryRun(1, 1000);
        $this->assertSame($oldSessions, $totalWouldBeDeleted);
        $this->assertSame($total, SessionFactory::count());
    }

    public function testPurgeSessionsService_Success_DryRunRespectsLimit()
    {
        $service = new PurgeSessionsService();

        // 10 old sessions
        $oldSessions = 10;
        SessionFactory::make($oldSessions)->modifiedAt(DateTime::now()->subDays(10))->persist();

        $limit = 3;

        $totalWouldBeDeleted = $service->dryRun(1, $limit);
        $this->assertSame($limit, $totalWouldBeDeleted);
        $this->assertSame($oldSessions, SessionFactory::count());
    }
}
