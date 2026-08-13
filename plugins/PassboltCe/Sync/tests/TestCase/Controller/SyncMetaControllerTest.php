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

use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use Passbolt\Sync\Service\SyncMetaService;
use Passbolt\Sync\SyncPlugin;

class SyncMetaControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(SyncPlugin::class);
    }

    public function testSyncMetaController_Error_NotAuthenticated(): void
    {
        $this->getJson('/sync/meta.json');
        $this->assertAuthenticationError();
    }

    public function testSyncMetaController_Success(): void
    {
        $this->logInAs(UserFactory::make()->user()->persist());

        $this->getJson('/sync/meta.json');
        $this->assertSuccess();

        $this->assertSame(SyncMetaService::PROTOCOL, $this->_responseJsonBody->protocol);
        $this->assertSame(SyncMetaService::MAX_PAGE_SIZE, $this->_responseJsonBody->max_page_size);
        $this->assertNotEmpty($this->_responseJsonBody->epoch);
        $this->assertGreaterThan(0, $this->_responseJsonBody->retention_days);
    }

    public function testSyncMetaController_Success_EpochIsStableAcrossCalls(): void
    {
        $this->logInAs(UserFactory::make()->user()->persist());

        $this->getJson('/sync/meta.json');
        $first = $this->_responseJsonBody->epoch;
        $this->getJson('/sync/meta.json');

        // a changing epoch would tell every client to throw away its cursor and bootstrap again
        $this->assertSame($first, $this->_responseJsonBody->epoch);
    }
}
