<?php
declare(strict_types=1);

/**
 * Passly ~ Open source password manager for teams
 * Copyright (c) Svaroh
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Svaroh
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://passly.svaroh.net Passly
 * @since         6.0.0
 */

namespace Passbolt\Sync\Command;

use App\Command\PassboltCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Passbolt\Sync\Service\SyncLogRetentionService;

/**
 * Prunes the change journal, meant to be run from cron.
 */
class SyncLogPruneCommand extends PassboltCommand
{
    private SyncLogRetentionService $retentionService;

    /**
     * @param \Passbolt\Sync\Service\SyncLogRetentionService|null $retentionService Retention service.
     */
    public function __construct(?SyncLogRetentionService $retentionService = null)
    {
        parent::__construct();
        $this->retentionService = $retentionService ?? new SyncLogRetentionService();
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Prune synchronisation journal entries older than the retention window.');
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription($this->getCommandDescription());

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        $days = $this->retentionService->retentionDays();
        $pruned = $this->retentionService->prune();

        $io->success(__('Pruned {0} journal entries older than {1} days.', $pruned, $days));

        return $this->successCode();
    }
}
