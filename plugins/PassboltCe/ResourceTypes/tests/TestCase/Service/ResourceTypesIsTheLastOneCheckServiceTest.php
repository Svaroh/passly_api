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

namespace Passbolt\ResourceTypes\Test\TestCase\Service;

use App\Test\Lib\AppTestCase;
use Passbolt\Metadata\Test\Factory\MetadataTypesSettingsFactory;
use Passbolt\ResourceTypes\Service\ResourceTypesIsTheLastOneCheckService;
use Passbolt\ResourceTypes\Test\Factory\ResourceTypeFactory;

/**
 * @covers \Passbolt\ResourceTypes\Service\ResourceTypesIsTheLastOneCheckService
 */
class ResourceTypesIsTheLastOneCheckServiceTest extends AppTestCase
{
    public function testIsTheOnlyOne_TrueWhenOnlyOneActive_IgnoringSoftDeleted(): void
    {
        /** @var \Passbolt\ResourceTypes\Model\Entity\ResourceType $active */
        $active = ResourceTypeFactory::make()->passwordString()->persist();
        ResourceTypeFactory::make()->passwordAndDescription()->deleted()->persist();
        ResourceTypeFactory::make()->v5PasswordString()->deleted()->persist();

        $this->assertTrue((new ResourceTypesIsTheLastOneCheckService())->isTheOnlyOne($active));
    }

    public function testIsTheOnlyOne_FalseWhenMultipleActive(): void
    {
        /** @var \Passbolt\ResourceTypes\Model\Entity\ResourceType $active */
        $active = ResourceTypeFactory::make()->passwordString()->persist();
        ResourceTypeFactory::make()->passwordAndDescription()->persist();
        // Soft-deleted noise; must not affect the outcome either way.
        ResourceTypeFactory::make()->v5PasswordString()->deleted()->persist();

        $this->assertFalse((new ResourceTypesIsTheLastOneCheckService())->isTheOnlyOne($active));
    }

    public function testIsLastOfTheDefaultVersion_V5Target_TrueWhenOnlyOneActiveNonV4_IgnoringSoftDeleted(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();

        /** @var \Passbolt\ResourceTypes\Model\Entity\ResourceType $active */
        $active = ResourceTypeFactory::make()->v5PasswordString()->persist();
        ResourceTypeFactory::make()->v5Default()->deleted()->persist();
        ResourceTypeFactory::make()->v5StandaloneTotp()->deleted()->persist();
        // Active v4 rows are ignored by the "default version" count (slug NOT IN v4Types).
        ResourceTypeFactory::make()->passwordString()->persist();

        $this->assertTrue((new ResourceTypesIsTheLastOneCheckService())->isLastOfTheDefaultVersion($active));
    }

    public function testIsLastOfTheDefaultVersion_V5Target_FalseWhenMultipleActiveNonV4(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();

        /** @var \Passbolt\ResourceTypes\Model\Entity\ResourceType $active */
        $active = ResourceTypeFactory::make()->v5PasswordString()->persist();
        ResourceTypeFactory::make()->v5Default()->persist();
        ResourceTypeFactory::make()->v5StandaloneTotp()->deleted()->persist();

        $this->assertFalse((new ResourceTypesIsTheLastOneCheckService())->isLastOfTheDefaultVersion($active));
    }

    public function testIsLastOfTheDefaultVersion_V4Target_ReturnsFalseRegardlessOfSeedData(): void
    {
        // Documents current behaviour: the version-family switch short-circuits for v4 targets and
        // never reaches the count. `isTheOnlyOne` is the guard that catches v4 targets in the delete flow.
        MetadataTypesSettingsFactory::make()->v4()->persist();

        /** @var \Passbolt\ResourceTypes\Model\Entity\ResourceType $target */
        $target = ResourceTypeFactory::make()->passwordString()->persist();
        ResourceTypeFactory::make()->passwordAndDescription()->deleted()->persist();
        ResourceTypeFactory::make()->standaloneTotp()->deleted()->persist();

        $this->assertFalse((new ResourceTypesIsTheLastOneCheckService())->isLastOfTheDefaultVersion($target));
    }
}
