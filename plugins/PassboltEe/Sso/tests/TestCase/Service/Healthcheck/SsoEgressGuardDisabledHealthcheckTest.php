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
 * @since         5.15.0
 */

namespace Passbolt\Sso\Test\TestCase\Service\Healthcheck;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use Cake\Core\Configure;
use Passbolt\Ee\Service\Healthcheck\EeHealthcheckServiceCollector;
use Passbolt\Sso\Service\Healthcheck\SsoEgressGuardDisabledHealthcheck;
use Passbolt\Sso\Test\Lib\SsoTestCase;
use Passbolt\Sso\Utility\Http\SsoEgressGuard;

/**
 * @covers \Passbolt\Sso\Service\Healthcheck\SsoEgressGuardDisabledHealthcheck
 */
class SsoEgressGuardDisabledHealthcheckTest extends SsoTestCase
{
    private SsoEgressGuardDisabledHealthcheck $sut;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sut = new SsoEgressGuardDisabledHealthcheck();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->sut);

        parent::tearDown();
    }

    public function testSsoEgressGuardDisabledHealthcheck_Fail_WithDefaultConfig(): void
    {
        $result = $this->sut->check();

        $this->assertFalse($result->isPassed());
        $this->assertSame(EeHealthcheckServiceCollector::DOMAIN_SSO, $result->domain());
        $this->assertSame(EeHealthcheckServiceCollector::DOMAIN_SSO, $result->cliOption());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_WARNING, $result->level());
        $this->assertStringContainsString('will be enabled by default', $result->getFailureMessage());
    }

    public function testSsoEgressGuardDisabledHealthcheck_Pass_WhenGuardEnabled(): void
    {
        Configure::write(SsoEgressGuard::CONFIG_ENABLED, true);

        $result = $this->sut->check();

        $this->assertTrue($result->isPassed());
    }
}
