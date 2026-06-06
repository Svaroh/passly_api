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
 * @since         4.0.0
 */

namespace Passbolt\TotpResourceTypes\Test\TestCase\Service;

use App\Test\Lib\AppTestCase;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use Passbolt\ResourceTypes\Model\Entity\ResourceType;
use Passbolt\ResourceTypes\Test\Scenario\ResourceTypesScenario;
use Passbolt\TotpResourceTypes\Service\TotpResourceTypesFinderService;
use Passbolt\TotpResourceTypes\Test\Scenario\TotpResourceTypesScenario;

/**
 * @covers \Passbolt\ResourceTypes\Service\ResourceTypesFinderService
 */
class TotpResourceTypesFinderServiceTest extends AppTestCase
{
    use ScenarioAwareTrait;

    /**
     * @var TotpResourceTypesFinderService
     */
    private $service;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new TotpResourceTypesFinderService();
    }

    public function testFindReturnsAllResourceTypesIncludeTotp()
    {
        $this->loadFixtureScenario(ResourceTypesScenario::class);
        $this->loadFixtureScenario(TotpResourceTypesScenario::class);

        $result = $this->service->find();
        $resourceTypeSlugs = $result
            ->all()
            ->extract('slug')
            ->toList();

        $this->assertContains(ResourceType::SLUG_STANDALONE_TOTP, $resourceTypeSlugs);
        $this->assertContains(ResourceType::SLUG_PASSWORD_DESCRIPTION_TOTP, $resourceTypeSlugs);
    }
}
