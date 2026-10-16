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

namespace Passbolt\Sync\Utility;

use App\Model\Entity\Role;
use App\Utility\UserAccessControl;
use App\Utility\UserAction;
use Cake\ORM\Locator\LocatorAwareTrait;
use Exception;

/**
 * Records secret access log entries when secrets are served over synchronisation endpoints.
 */
trait SyncSecretAccessLoggerTrait
{
    use LocatorAwareTrait;

    /**
     * @param string $userId Accessing user identifier.
     * @param array<array<string, mixed>> $secrets Secrets served to the client.
     * @return void
     */
    protected function logSecretAccesses(string $userId, array $secrets): void
    {
        if (empty($secrets)) {
            return;
        }

        /** @var \App\Model\Table\SecretsTable $secretsTable */
        $secretsTable = $this->fetchTable('Secrets');
        if (!$secretsTable->hasAssociation('SecretAccesses')) {
            return;
        }

        try {
            $uac = UserAction::getInstance()->getUserAccessControl();
        } catch (Exception) {
            $uac = new UserAccessControl(Role::USER, $userId);
        }

        /** @var \Passbolt\Log\Model\Table\SecretAccessesTable $secretAccessesTable */
        $secretAccessesTable = $this->fetchTable('Passbolt/Log.SecretAccesses');

        foreach ($secrets as $secret) {
            $secretAccessesTable->createFromSecretDetails(
                $uac,
                (string)$secret['resource_id'],
                (string)$secret['id'],
            );
        }
    }
}
