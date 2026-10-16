<?php
declare(strict_types=1);

/**
 * Passly ~ Open source password manager for teams
 * Copyright (c) Svaroh
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Svaroh
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://passly.svaroh.net Passly
 * @since         6.0.0
 */

namespace Passbolt\Sync\Test\TestCase\Controller;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use Passbolt\Sync\SyncPlugin;

class SyncManifestControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(SyncPlugin::class);
    }

    public function testSyncManifestController_Error_NotAuthenticated(): void
    {
        $this->getJson('/sync/manifest.json');
        $this->assertAuthenticationError();
    }

    public function testSyncManifestController_Error_UnsupportedType(): void
    {
        $this->logInAsUser();
        $this->getJson('/sync/manifest.json?type=folders');
        $this->assertBadRequestError('The entity type is not supported.');
    }

    public function testSyncManifestController_Success_DefaultTypeResources(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->persist();

        $this->logInAs($user);
        $this->getJson('/sync/manifest.json');
        $this->assertSuccess();

        $body = (array)$this->_responseJsonBody;
        $this->assertNotEmpty($body);

        $resourceIds = array_column($body, 'id');
        $this->assertContains($resource->get('id'), $resourceIds);

        foreach ($body as $item) {
            $record = (array)$item;
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('modified', $record);
            $this->assertArrayHasKey('secret_modified', $record);
            $this->assertNotEmpty($record['modified']);
            if ($record['id'] === $resource->get('id')) {
                $this->assertNotNull($record['secret_modified']);
            }
        }
    }

    public function testSyncManifestController_Success_ExplicitTypeResources(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->logInAs($user);

        $this->getJson('/sync/manifest.json?type=resources');
        $this->assertSuccess();
        $this->assertIsArray((array)$this->_responseJsonBody);
    }
}
