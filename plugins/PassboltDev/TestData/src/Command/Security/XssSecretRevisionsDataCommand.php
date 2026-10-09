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
 * @since         5.14.0
 */
namespace Passbolt\TestData\Command\Security;

use App\Utility\UuidFactory;
use Passbolt\TestData\Command\Base\SecretRevisionsDataCommand;
use Passbolt\TestData\Lib\Security\Xss;

class XssSecretRevisionsDataCommand extends SecretRevisionsDataCommand
{
    protected bool $_truncate = false;

    /**
     * Get the secret revisions data
     *
     * @return array
     */
    public function getData(): array
    {
        $exploits = Xss::getExploits();
        $max = count($exploits);
        $secretRevisions = [];

        for ($i = 0; $i < $max; $i++) {
            $secretRevisions[] = [
                'id' => UuidFactory::uuid('$secretRevision.id.xss' . count($secretRevisions)),
                'resource_id' => UuidFactory::uuid('resource.id.xss' . count($secretRevisions)),
                'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
                'deleted' => null,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
                'created_by' => UuidFactory::uuid('user.id.xss' . count($secretRevisions)),
                'modified_by' => UuidFactory::uuid('user.id.xss' . count($secretRevisions)),
            ];
        }

        return $secretRevisions;
    }
}
