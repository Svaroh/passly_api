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
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use Cake\ORM\TableRegistry;
use Passbolt\Sync\Model\Table\SyncLogTable;
use Passbolt\Sync\Service\SyncMetaService;
use Passbolt\Sync\SyncPlugin;

class SyncBootstrapControllerTest extends AppIntegrationTestCase
{
    private SyncLogTable $SyncLog;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(SyncPlugin::class);
        RoleFactory::make()->guest()->persist();
        /** @var \Passbolt\Sync\Model\Table\SyncLogTable $syncLog */
        $syncLog = TableRegistry::getTableLocator()->get('Passbolt/Sync.SyncLog');
        $this->SyncLog = $syncLog;
    }

    public function testSyncBootstrapController_Error_NotAuthenticated(): void
    {
        $this->getJson('/sync/bootstrap.json');
        $this->assertAuthenticationError();
    }

    public function testSyncBootstrapController_Error_InvalidPage(): void
    {
        $this->logInAsUser();
        $this->getJson('/sync/bootstrap.json?page=0');
        $this->assertBadRequestError('The page should be a positive integer.');
    }

    public function testSyncBootstrapController_Error_InvalidLimit(): void
    {
        $this->logInAsUser();
        $this->getJson('/sync/bootstrap.json?limit=501');
        $this->assertBadRequestError('The limit should be an integer between 1 and ' . SyncMetaService::MAX_PAGE_SIZE . '.');
    }

    public function testSyncBootstrapController_Error_InvalidCursor(): void
    {
        $this->logInAsUser();
        $this->getJson('/sync/bootstrap.json?cursor=-5');
        $this->assertBadRequestError('The cursor should be a positive integer.');
    }

    public function testSyncBootstrapController_Success_PageOne_ReturnsSnapshotWithPagination(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->persist();

        $this->logInAs($user);
        $this->getJson('/sync/bootstrap.json');
        $this->assertSuccess();

        // Assert pagination header
        $header = $this->_responseJsonHeader;
        $this->assertNotNull($header);
        $this->assertObjectHasProperty('pagination', $header);
        $this->assertSame(1, $header->pagination->page);
        $this->assertSame(SyncMetaService::MAX_PAGE_SIZE, $header->pagination->limit);
        $this->assertGreaterThanOrEqual(1, $header->pagination->count);

        // Assert body structure
        $body = $this->_responseJsonBody;
        $this->assertNotNull($body);
        $this->assertObjectHasProperty('cursor', $body);
        $this->assertObjectHasProperty('epoch', $body);
        $this->assertObjectHasProperty('upserts', $body);

        $upserts = $body->upserts;
        $this->assertObjectHasProperty('resources', $upserts);
        $this->assertObjectHasProperty('secrets', $upserts);
        $this->assertObjectHasProperty('folders', $upserts);
        $this->assertObjectHasProperty('users', $upserts);
        $this->assertObjectHasProperty('groups', $upserts);
        $this->assertObjectHasProperty('resource_types', $upserts);
        $this->assertObjectHasProperty('metadata_keys', $upserts);

        $resourceIds = array_column((array)$upserts->resources, 'id');
        $this->assertContains($resource->get('id'), $resourceIds);

        $secretResourceIds = array_column((array)$upserts->secrets, 'resource_id');
        $this->assertContains($resource->get('id'), $secretResourceIds);
    }

    public function testSyncBootstrapController_Success_CustomPageAndLimit_PreservesCursor(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->logInAs($user);

        $this->getJson('/sync/bootstrap.json?page=2&limit=50&cursor=42');
        $this->assertSuccess();

        $header = $this->_responseJsonHeader;
        $this->assertNotNull($header);
        $this->assertSame(2, $header->pagination->page);
        $this->assertSame(50, $header->pagination->limit);

        $body = $this->_responseJsonBody;
        $this->assertSame(42, $body->cursor);

        // On page > 1 directories are empty
        $upserts = $body->upserts;
        $this->assertEmpty((array)$upserts->folders);
        $this->assertEmpty((array)$upserts->users);
        $this->assertEmpty((array)$upserts->groups);
        $this->assertEmpty((array)$upserts->resource_types);
        $this->assertEmpty((array)$upserts->metadata_keys);
    }
}
