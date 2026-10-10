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
namespace Passbolt\JwtAuthentication\Test\TestCase\Controller\Resources;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\JwtAuthentication\Test\Utility\JwtAuthenticationIntegrationTestCase;

class JwtAccessTokenResourcesIndexControllerTest extends JwtAuthenticationIntegrationTestCase
{
    use LocatorAwareTrait;

    /**
     * @return void
     */
    public function testJwtAccessTokenResourcesIndexController_Error_CannotReadSecretsWhenUserDisabled(): void
    {
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make()->user()->disabled()->persist();
        ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->persist();

        $this->createJwtTokenAndSetInHeader($user->id);
        $this->getJson('/resources.json?contain[secret]=1');

        $this->assertResponseError('Authentication is required to continue');
    }
}
