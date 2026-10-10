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
namespace App\Test\TestCase\Service\Healthcheck\Database;

use App\Service\Cache\ModelCacheCheckService;
use App\Service\Healthcheck\Database\ModelCacheDatabaseHealthcheck;
use Cake\Cache\Cache;
use Cake\Cache\Engine\ArrayEngine;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/**
 * @covers \App\Service\Healthcheck\Database\ModelCacheDatabaseHealthcheck
 */
class ModelCacheDatabaseHealthcheckTest extends TestCase
{
    /**
     * The original `_cake_model_` cache configuration, restored in tearDown().
     *
     * @var mixed
     */
    private mixed $originalCacheConfig = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        // Redirect the cache to an in-memory Array engine for the duration of the test
        $this->originalCacheConfig = Cache::getConfig(ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY);
        Cache::drop(ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY);
        Cache::setConfig(ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY, ['className' => ArrayEngine::class]);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        // Restore the original model cache configuration.
        Cache::drop(ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY);
        if ($this->originalCacheConfig !== null) {
            Cache::setConfig(ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY, $this->originalCacheConfig);
        }
        parent::tearDown();
    }

    public function testModelCacheDatabaseHealthcheck_NoDiscrepancy_PassesWithSuccessMessage(): void
    {
        $healthcheck = (new ModelCacheDatabaseHealthcheck())->check();

        $this->assertTrue($healthcheck->isPassed());
        $this->assertStringContainsString('no discrepancies', $healthcheck->getSuccessMessage());
    }

    public function testModelCacheDatabaseHealthcheck_CacheNotConfigured_Passes(): void
    {
        // This healthcheck passes when the model cache is not configured (or deliberately disabled): with no cached
        // schema there is nothing that can drift from the database, so there is no discrepancy to warn about.
        Cache::drop(ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY);

        $healthcheck = (new ModelCacheDatabaseHealthcheck())->check();

        $this->assertTrue($healthcheck->isPassed());
    }

    public function testModelCacheDatabaseHealthcheck_DatabaseUnreachable_StaysSilent(): void
    {
        // When the database is unreachable the check cannot compare anything but check must pass since it is normal
        // to hold no cache if the DB is not working
        ConnectionManager::setConfig('invalid_model_cache', ['url' => 'mysql://foo:bar@localhost/invalid_database']);
        try {
            $healthcheck = new ModelCacheDatabaseHealthcheck();
            $healthcheck->setOptions(['datasource' => 'invalid_model_cache']);
            $healthcheck->check();

            $this->assertTrue($healthcheck->isPassed());
        } finally {
            ConnectionManager::drop('invalid_model_cache');
        }
    }
}
