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
namespace Passbolt\TestData\Command\Large;

use App\Utility\UuidFactory;
use Passbolt\TestData\Lib\DataCommand;

class SecretRevisionsDataCommand extends DataCommand
{
    public string $entityName = 'SecretRevisions';

    /**
     * Get the secret revisions data
     *
     * @return array
     */
    public function getData(): array
    {
        /** @var \App\Model\Table\ResourcesTable $resourcesTable */
        $resourcesTable = $this->fetchTable('Resources');
        $resources = $resourcesTable->find('all');

        $secretRevisions = [];

        foreach ($resources as $resource) {
            $secretRevisions[] = [
                'id' => UuidFactory::uuid("secretRevision.id.{$resource->get('id')}"),
                'resource_id' => $resource->get('id'),
                'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
                'deleted' => null,
                'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
                'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
                'created_by' => $resource->get('created_by'),
                'modified_by' => $resource->get('modified_by'),
            ];
        }

        return $secretRevisions;
    }
}
