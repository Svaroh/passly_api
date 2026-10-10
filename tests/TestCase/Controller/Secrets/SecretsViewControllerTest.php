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
 * @since         2.7.0
 */

namespace App\Test\TestCase\Controller\Secrets;

use App\Model\Entity\Permission;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;

class SecretsViewControllerTest extends AppIntegrationTestCase
{
    public function testSecretsViewController_Success(): void
    {
        $user = UserFactory::make()->user()->persist();
        $owner = UserFactory::make()->admin()->persist();

        $this->loginAs($user);

        $resourceId = ResourceFactory::make()->withPermissionsFor([$owner], Permission::OWNER)->withSecretsFor([$user])->withPermissionsFor([$user], Permission::READ)->persist()->get('id');

        $this->getJson("/secrets/resource/$resourceId.json");
        $this->assertSuccess();
        $this->assertNotNull($this->_responseJsonBody);
        $this->assertSecretAttributes($this->_responseJsonBody);
    }

    public function testSecretsViewController_Error_NotAuthenticated(): void
    {
        $admin = UserFactory::make()->admin()->persist();

        $resourceId = ResourceFactory::make()->withCreatorAndPermission($admin)->persist()->get('id');

        $this->getJson("/secrets/resource/$resourceId.json");
        $this->assertAuthenticationError();
    }

    public function testSecretsViewController_Error_NotValidId(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->loginAs($user);

        $resourceId = 'invalid-id';

        $this->getJson("/secrets/resource/$resourceId.json");
        $this->assertError(400, 'The resource identifier should be a valid UUID.');
    }

    public function testSecretsViewController_Error_NotFound(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->loginAs($user);

        $resourceOwner = UserFactory::make()->user()->persist();
        $resourceId = ResourceFactory::make()->withCreatorAndPermission($resourceOwner)->withPermissionsFor([$user], Permission::READ)->persist()->get('id');

        $this->getJson("/secrets/resource/$resourceId.json");
        $this->assertError(404, 'The secret does not exist.');
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testSecretsViewController_Error_NotJson(): void
    {
        $user = UserFactory::make()->persist();
        $resourceId = ResourceFactory::make()->withCreatorAndPermission($user)->persist()->get('id');
        $this->logInAs($user);

        $this->get("/secrets/resource/{$resourceId}");

        $this->assertResponseCode(404);
    }

    public function testSecretsViewController_RefusesUserWithoutCurrentResourcePermission(): void
    {
        $user = UserFactory::make()->user()->persist();
        $owner = UserFactory::make()->admin()->persist();

        // Seed the stale-row scenario: user still has a Secrets row but no Permissions row on
        // the resource — the state a broken cleanup would leave behind.
        $resourceId = ResourceFactory::make()
            ->withPermissionsFor([$owner], Permission::OWNER)
            ->withSecretsFor([$user])
            ->persist()
            ->get('id');

        $this->loginAs($user);
        $this->getJson("/secrets/resource/$resourceId.json");

        // NotFoundException, not ForbiddenException, to keep the shape identical to the
        // "secret missing" path and not leak resource existence to a revoked user.
        $this->assertError(404, 'The secret does not exist.');
    }

    public function testSecretsViewController_AllowsUserWithCurrentResourcePermission(): void
    {
        $user = UserFactory::make()->user()->persist();
        $owner = UserFactory::make()->admin()->persist();

        $resourceId = ResourceFactory::make()
            ->withPermissionsFor([$owner], Permission::OWNER)
            ->withPermissionsFor([$user], Permission::READ)
            ->withSecretsFor([$user])
            ->persist()
            ->get('id');

        $this->loginAs($user);
        $this->getJson("/secrets/resource/$resourceId.json");

        $this->assertSuccess();
        $this->assertNotNull($this->_responseJsonBody);
        $this->assertSecretAttributes($this->_responseJsonBody);
    }
}
