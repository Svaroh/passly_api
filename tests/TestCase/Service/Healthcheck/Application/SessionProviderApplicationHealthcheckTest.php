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
namespace App\Test\TestCase\Service\Healthcheck\Application;

use App\Service\Healthcheck\Application\SessionProviderApplicationHealthcheck;
use App\Service\Healthcheck\HealthcheckServiceCollector;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * @covers \App\Service\Healthcheck\Application\SessionProviderApplicationHealthcheck
 */
class SessionProviderApplicationHealthcheckTest extends TestCase
{
    private SessionProviderApplicationHealthcheck $sut;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sut = new SessionProviderApplicationHealthcheck();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->sut);

        parent::tearDown();
    }

    public static function sessionPresetProvider(): array
    {
        return [
            'php preset' => ['php'],
            'cache preset' => ['cache'],
            'database preset' => ['database'],
            'cake preset' => ['cake'],
        ];
    }

    /**
     * @dataProvider sessionPresetProvider
     */
    public function testSessionProviderApplicationHealthcheck_Success(string $preset): void
    {
        Configure::write('Session.defaults', $preset);

        $result = $this->sut->check();

        $this->assertTrue($result->isPassed());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_APPLICATION, $result->domain());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_APPLICATION, $result->cliOption());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_ERROR, $result->level());
        $this->assertTextEquals("The session provider is $preset.", $result->getSuccessMessage());
    }

    public function testSessionProviderApplicationHealthcheck_Error_NotSupportedProvider(): void
    {
        $wrongProvider = 'Foo';
        Configure::write('Session.defaults', $wrongProvider);

        $result = $this->sut->check();
        $supportedProviders = implode(', ', $result::SESSION_PROVIDERS_AVAILABLE);
        $sessionConfig = $result::SESSION_PROVIDER_CONFIG;
        $sessionConfigPath = $result::SESSION_PROVIDER_CONFIG_PATH;

        $this->assertFalse($result->isPassed());
        $this->assertTextEquals("The session provider $wrongProvider is not supported.", $result->getFailureMessage());
        $this->assertTextContains("The session providers supported are: $supportedProviders.", $result->getHelpMessage()[0]);
        $this->assertTextContains("Define it using the SESSION_DEFAULTS environment variable or under $sessionConfig in $sessionConfigPath.", $result->getHelpMessage()[1]);
    }
}
