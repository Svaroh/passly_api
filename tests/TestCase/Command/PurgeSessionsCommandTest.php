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
namespace App\Test\TestCase\Command;

use App\Test\Factory\SessionFactory;
use App\Test\Lib\AppTestCase;
use App\Test\Lib\Utility\PassboltCommandTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\I18n\DateTime;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class PurgeSessionsCommandTest extends AppTestCase
{
    use ConsoleIntegrationTestTrait;
    use TruncateDirtyTables;
    use PassboltCommandTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mockProcessUserService('www-data');
    }

    public function testPurgeSessionsCommand_Help()
    {
        $this->exec('passbolt purge_sessions -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Purge sessions.');
        $this->assertOutputContains('<warning>The performance of your instance might be degraded');
        $this->assertOutputContains('--dry-run, -d');
        $this->assertOutputContains('--retention-in-days, -r');
        $this->assertOutputContains('--limit, -l');
    }

    /**
     * Will fail if run as root
     */
    public function testPurgeSessionsCommand_Root()
    {
        $this->assertCommandCannotBeRunAsRootUser('purge_sessions -r 1');
    }

    public function testPurgeSessionsCommand_Success_DeletesRowsOlderThanRetention()
    {
        $retentionDays = 3;
        // 5 old sessions
        SessionFactory::make(5)->modifiedAt(DateTime::now()->subDays($retentionDays + 1))->persist();

        // 5 fresh sessions
        SessionFactory::make(5)->modifiedNow()->persist();

        $this->exec('passbolt purge_sessions -r ' . $retentionDays);
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>5 session rows were deleted.</success>');
        $this->assertSame(5, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Success_ZeroRetentionDeletesEveryRow()
    {
        // 10 old sessions
        SessionFactory::make(10)->modifiedAt(DateTime::now()->subDays(10))->persist();

        // 5 fresh sessions
        SessionFactory::make(5)->modifiedNow()->persist();

        $this->exec('passbolt purge_sessions -r 0');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>15 session rows were deleted.</success>');
        $this->assertSame(0, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Success_ReportsZeroWhenNothingToDelete()
    {
        $this->exec('passbolt purge_sessions -r 0');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>0 session rows were deleted.</success>');
        $this->assertSame(0, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Success_LimitCapsDeletionAndPrefersOldestRows()
    {
        $oldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(10))->persist();
        $secondOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(8))->persist();
        $thirdOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(6))->persist();
        $fourthOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(4))->persist();

        $this->exec('passbolt purge_sessions -r 1 -l 2');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>2 session rows were deleted.</success>');
        $this->assertSame(2, SessionFactory::count());
        $this->assertGreaterThan($oldest->modified, $thirdOldest->modified);
        $this->assertGreaterThan($oldest->modified, $fourthOldest->modified);
        $this->assertGreaterThan($secondOldest->modified, $thirdOldest->modified);
        $this->assertGreaterThan($secondOldest->modified, $fourthOldest->modified);
        $this->assertNotNull(SessionFactory::find()->where(['id' => $thirdOldest->id])->first());
        $this->assertNotNull(SessionFactory::find()->where(['id' => $fourthOldest->id])->first());
        $this->assertNull(SessionFactory::find()->where(['id' => $oldest->id])->first());
        $this->assertNull(SessionFactory::find()->where(['id' => $secondOldest->id])->first());
    }

    public function testPurgeSessionsCommand_Success_LimitInteractsWithZeroRetention()
    {
        $oldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(10))->persist();
        $secondOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(8))->persist();
        $thirdOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(6))->persist();
        $fourthOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(4))->persist();
        $fifthOldest = SessionFactory::make()->modifiedAt(DateTime::now()->subDays(5))->persist();

        $this->exec('passbolt purge_sessions -r 0 -l 2');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>2 session rows were deleted.</success>');
        $this->assertSame(3, SessionFactory::count());
        $this->assertGreaterThan($oldest->modified, $thirdOldest->modified);
        $this->assertGreaterThan($oldest->modified, $fourthOldest->modified);
        $this->assertGreaterThan($oldest->modified, $fifthOldest->modified);
        $this->assertGreaterThan($secondOldest->modified, $thirdOldest->modified);
        $this->assertGreaterThan($secondOldest->modified, $fourthOldest->modified);
        $this->assertGreaterThan($secondOldest->modified, $fifthOldest->modified);
        $this->assertNotNull(SessionFactory::find()->where(['id' => $thirdOldest->id])->first());
        $this->assertNotNull(SessionFactory::find()->where(['id' => $fourthOldest->id])->first());
        $this->assertNotNull(SessionFactory::find()->where(['id' => $fifthOldest->id])->first());
        $this->assertNull(SessionFactory::find()->where(['id' => $oldest->id])->first());
        $this->assertNull(SessionFactory::find()->where(['id' => $secondOldest->id])->first());
    }

    public function testPurgeSessionsCommand_Success_DryRunReportsCountWithoutDeleting()
    {
        // 5 old sessions
        SessionFactory::make(5)->modifiedAt(DateTime::now()->subDays(8))->persist();

        // 5 fresh sessions
        SessionFactory::make(5)->modifiedNow()->persist();

        $this->exec('passbolt purge_sessions -r 1 -d');
        $this->assertExitSuccess();
        $this->assertOutputContains('<info>5 session rows would be deleted.</info>');
        $this->assertSame(10, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Success_DryRunRespectsLimit()
    {
        // 5 old sessions
        SessionFactory::make(5)->modifiedAt(DateTime::now()->subDays(5))->persist();

        $this->exec('passbolt purge_sessions -r 1 -l 2 -d');
        $this->assertExitSuccess();
        $this->assertOutputContains('<info>2 session rows would be deleted.</info>');
        $this->assertSame(5, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Success_DryRunZeroRetentionReportsTotalWithoutDeleting()
    {
        // 1000 old sessions
        SessionFactory::make(1000)->modifiedAt(DateTime::now()->subDays(30))->persist();

        $this->exec('passbolt purge_sessions -r 0 -d');
        $this->assertExitSuccess();
        $this->assertOutputContains('<info>1000 session rows would be deleted.</info>');
        $this->assertSame(1000, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Error_NegativeRetention()
    {
        // 10 old sessions
        SessionFactory::make(10)->modifiedAt(DateTime::now()->subDays(10))->persist();

        $this->exec('passbolt purge_sessions -r -1 -d');
        $this->assertExitError();
        $this->assertOutputContains('<error>Retention in days option must be greater than or equal to zero.</error>');
        $this->assertSame(10, SessionFactory::count());
    }

    public function testPurgeSessionsCommand_Error_ZeroOrNegativeLimit()
    {
        // 10 old sessions
        SessionFactory::make(10)->modifiedAt(DateTime::now()->subDays(8))->persist();

        $this->exec('passbolt purge_sessions -r 0 -l 0');
        $this->assertExitError();
        $this->assertOutputContains('<error>Limit option must be greater than zero.</error>');

        $this->exec('passbolt purge_sessions -r 0 -l -1');
        $this->assertExitError();
        $this->assertOutputContains('<error>Limit option must be greater than zero.</error>');

        $this->assertSame(10, SessionFactory::count());
    }
}
