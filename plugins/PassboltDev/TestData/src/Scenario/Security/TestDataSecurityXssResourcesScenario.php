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
namespace Passbolt\TestData\Scenario\Security;

use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use Passbolt\Folders\Test\Factory\ResourceFactory;
use Passbolt\SecretRevisions\Test\Factory\SecretRevisionFactory;
use Passbolt\TestData\Command\Security\XssResourcesDataCommand;

class TestDataSecurityXssResourcesScenario implements FixtureScenarioInterface
{
    /**
     * @param mixed ...$args
     * @return array
     */
    public function load(mixed ...$args): array
    {
        $data = (new XssResourcesDataCommand())->getData();
        $resources = [];

        foreach ($data as $resource) {
            $secretRevisionFactory = SecretRevisionFactory::make()
                ->resourceId($resource['id'])
                ->resourceTypeId($resource['resource_type_id']);
            $resources[] = ResourceFactory::make($resource)->withSecretRevisions($secretRevisionFactory)->persist();
        }

        return $resources;
    }
}
