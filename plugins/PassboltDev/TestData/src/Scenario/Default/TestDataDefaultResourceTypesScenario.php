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
namespace Passbolt\TestData\Scenario\Default;

use Cake\Log\Log;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use Exception;
use Passbolt\ResourceTypes\Test\Factory\ResourceTypeFactory;
use Passbolt\TestData\Command\Base\ResourceTypesDataCommand;

class TestDataDefaultResourceTypesScenario implements FixtureScenarioInterface
{
    /**
     * @param mixed ...$args
     * @return array
     */
    public function load(mixed ...$args): array
    {
        $data = (new ResourceTypesDataCommand())->getData();
        $resourceTypes = [];

        try {
            $resourceTypes[] = ResourceTypeFactory::make($data)->persist();
        } catch (Exception $e) {
            Log::debug('Resource types are already persisted in DB.');

            return [];
        }

        return $resourceTypes;
    }
}
