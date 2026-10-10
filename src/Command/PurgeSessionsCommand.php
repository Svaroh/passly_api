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
namespace App\Command;

use App\Service\Command\ProcessUserService;
use App\Service\Sessions\PurgeSessionsService;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class PurgeSessionsCommand extends PassboltCommand
{
    /**
     * @var \App\Service\Command\ProcessUserService
     */
    protected ProcessUserService $processUserService;

    protected PurgeSessionsService $purgeSessionsService;

    public const DEFAULT_LIMIT = '100000'; // 100k

    /**
     * @param \App\Service\Command\ProcessUserService $processUserService Process user service
     */
    public function __construct(ProcessUserService $processUserService)
    {
        parent::__construct();
        $this->processUserService = $processUserService;
        $this->purgeSessionsService = new PurgeSessionsService();
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Purge sessions content.');
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription([
                __('Purge sessions.'),
                '<warning>' .
                __('The performance of your instance might be degraded while the command is running.')
                . '</warning>',
            ])
            ->addOption('retention-in-days', [
                'short' => 'r',
                'required' => true,
                'help' => __('Retention period in days. 0 purges every session row.'),
            ])
            ->addOption('dry-run', [
                'short' => 'd',
                'boolean' => true,
                'default' => false,
                'help' => __('Report the row count that would be deleted without touching the table.'),
            ])
            ->addOption('limit', [
                'short' => 'l',
                'required' => true,
                'default' => self::DEFAULT_LIMIT,
                'help' => __('Maximum number of rows to purge per run. Oldest rows are deleted first.'),
            ])
            ->removeOption('verbose');

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        // Root user is not allowed to execute this command.
        $this->assertCurrentProcessUser($io, $this->processUserService);

        $this->validateOptions($args, $io);

        $retentionInDays = (int)$args->getOption('retention-in-days');
        $limit = (int)$args->getOption('limit');
        $dryRun = $args->getOption('dry-run');
        if ($dryRun) {
            $this->getDryRun($retentionInDays, $limit, $io);
        } else {
            $this->purge($retentionInDays, $limit, $io);
        }

        return $this->successCode();
    }

    /**
     * Validates user provided arguments/options data.
     *
     * @param \Cake\Console\Arguments $args Argument object.
     * @param \Cake\Console\ConsoleIo $io I/O object.
     * @return void
     */
    private function validateOptions(Arguments $args, ConsoleIo $io): void
    {
        $errors = [];

        $retentionInDays = (int)$args->getOption('retention-in-days');
        if ($retentionInDays < 0) {
            $errors[] = __('Retention in days option must be greater than or equal to zero.');
        }

        $limit = (int)$args->getOption('limit');
        if ($limit < 1) {
            $errors[] = __('Limit option must be greater than zero.');
        }

        if (empty($errors)) {
            // No errors, data is good to use
            return;
        }

        foreach ($errors as $error) {
            $this->error($error, $io);
        }
        $this->abort();
    }

    /**
     * @param int $retentionInDays retention in days
     * @param int $limit Maximum number of rows to purge.
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return void
     */
    private function getDryRun(int $retentionInDays, int $limit, ConsoleIo $io): void
    {
        $totalToPurge = $this->purgeSessionsService->dryRun($retentionInDays, $limit);
        $io->info(__('{0} session rows would be deleted.', $totalToPurge));
    }

    /**
     * @param int $retentionInDays retention in days
     * @param int $limit Maximum number of rows to purge.
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return void
     */
    private function purge(int $retentionInDays, int $limit, ConsoleIo $io): void
    {
        $nEntriesDeleted = $this->purgeSessionsService->purge($retentionInDays, $limit);
        $io->success(__('{0} session rows were deleted.', $nEntriesDeleted));
    }
}
