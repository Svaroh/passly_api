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
 */

namespace App\Test\TestCase\Controller\Resources;

use App\Model\Entity\Permission;
use App\Test\Factory\PermissionFactory;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\SecretFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\UuidFactory;
use Cake\I18n\DateTime;
use Passbolt\Folders\FoldersPlugin;
use Passbolt\ResourceTypes\Test\Factory\ResourceTypeFactory;
use Passbolt\SecretRevisions\Test\Factory\SecretRevisionFactory;

/**
 * @covers \App\Controller\Resources\ResourcesRestoreController
 */
class ResourcesRestoreControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(FoldersPlugin::class);
        RoleFactory::make()->guest()->persist();
    }

    public function testResourcesRestoreController_Success(): void
    {
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->withSecretRevisions()
            ->persist()
            ->id;

        $this->deleteJson("/resources/$resourceId.json?recoverable=1");
        $this->assertSuccess();

        $this->postJson("/resources/$resourceId/restore.json");
        $this->assertSuccess();

        $resource = ResourceFactory::get($resourceId);
        $this->assertFalse($resource->deleted);
        $this->assertSame(1, PermissionFactory::find()->where(['aco_foreign_key' => $resourceId])->count());
        $this->assertSame(1, SecretFactory::find()->where([
            'resource_id' => $resourceId,
            'deleted IS' => null,
        ])->count());
        $this->assertSame(1, SecretRevisionFactory::find()->where([
            'resource_id' => $resourceId,
            'deleted IS' => null,
        ])->count());
    }

    public function testResourcesRestoreController_Error_NotDeleted(): void
    {
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->withSecretRevisions()
            ->persist()
            ->id;

        $this->postJson("/resources/$resourceId/restore.json");
        $this->assertError(400, 'The resource is not deleted.');
    }

    public function testResourcesRestoreController_Error_ReadAccess(): void
    {
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()
            ->withPermissionsFor([$user], Permission::READ)
            ->withSecretsFor([$user])
            ->withSecretRevisions()
            ->persist()
            ->id;
        $resource = ResourceFactory::get($resourceId, contain: ['ResourceTypes']);
        $resourcesTable = $this->fetchTable('Resources');
        $resourcesTable->softDelete($user->id, $resource, checkPermission: false, recoverable: true);

        $this->postJson("/resources/$resourceId/restore.json");
        $this->assertError(403, 'You do not have the permission to restore this resource.');
    }

    public function testResourcesRestoreController_Error_NonRecoverableDelete(): void
    {
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->withSecretRevisions()
            ->persist()
            ->id;

        $this->deleteJson("/resources/$resourceId.json");
        $this->assertSuccess();

        $this->postJson("/resources/$resourceId/restore.json");
        $this->assertError(404, 'The resource does not exist.');
    }

    public function testResourcesRestoreController_Error_ResourceTypeDeleted(): void
    {
        $user = $this->logInAsUser();
        $deleted = DateTime::yesterday();
        $resourceId = ResourceFactory::make()
            ->setDeleted()
            ->withPermissionsFor([$user])
            ->with('Secrets', SecretFactory::make([
                'user_id' => $user->id,
                'created_by' => $user->id,
                'modified_by' => $user->id,
            ])->deleted($deleted))
            ->with('SecretRevisions', SecretRevisionFactory::make()->deleted($deleted))
            ->with('ResourceTypes', ResourceTypeFactory::make()->passwordAndDescription()->deleted())
            ->persist()
            ->id;

        $this->postJson("/resources/$resourceId/restore.json");
        $this->assertError(400, 'Could not restore the resource.');
    }

    public function testResourcesRestoreController_Error_NotAuthenticated(): void
    {
        $resourceId = UuidFactory::uuid();
        $this->postJson("/resources/$resourceId/restore.json");
        $this->assertAuthenticationError();
    }

    public function testResourcesRestoreController_Error_NotJson(): void
    {
        $this->logInAsUser();
        $resourceId = UuidFactory::uuid();
        $this->post("/resources/$resourceId/restore");
        $this->assertResponseCode(404);
    }
}
