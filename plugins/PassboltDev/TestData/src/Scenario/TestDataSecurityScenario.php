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
namespace Passbolt\TestData\Scenario;

use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use Exception;
use Passbolt\TestData\Scenario\Default\TestDataDefaultResourceTypesScenario;
use Passbolt\TestData\Scenario\Default\TestDataDefaultRolesScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssCommentsScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssGpgKeysScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssGroupsScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssGroupsUsersScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssPermissionsScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssResourcesScenario;
use Passbolt\TestData\Scenario\Security\TestDataSecurityXssUsersScenario;

class TestDataSecurityScenario implements FixtureScenarioInterface
{
    /**
     * Load all default test data scenarios
     *
     * @param mixed ...$args
     * @return array
     */
    public function load(mixed ...$args): array
    {
        $results = [];
        $scenarios = [
            TestDataDefaultRolesScenario::class,
            TestDataDefaultResourceTypesScenario::class,
            TestDataSecurityXssUsersScenario::class,
            TestDataSecurityXssGpgKeysScenario::class,
            TestDataSecurityXssGroupsScenario::class,
            TestDataSecurityXssGroupsUsersScenario::class,
            TestDataSecurityXssResourcesScenario::class,
            TestDataSecurityXssPermissionsScenario::class,
            TestDataSecurityXssCommentsScenario::class,
            //TestDataDemoSecretsScenario::class,
        ];

        foreach ($scenarios as $scenarioClass) {
            try {
                /** @var \CakephpFixtureFactories\Scenario\FixtureScenarioInterface $scenario */
                $scenario = new $scenarioClass();
                $results[] = $scenario->load(...$args);
            } catch (Exception $e) {
                throw new Exception("Failed to load scenario: {$scenarioClass}. Reason: " . $e->getMessage(), 0, $e);
            }
        }

        return array_merge(...$results);
    }
}
