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
namespace App\Test\TestCase\Service\Cache;

use App\Service\Cache\ModelCacheCheckService;
use Cake\Cache\Cache;
use Cake\Cache\Engine\ArrayEngine;
use Cake\Database\Schema\Collection as SchemaCollection;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/**
 * @covers \App\Service\Cache\ModelCacheCheckService
 */
class ModelCacheCheckServiceTest extends TestCase
{
    private const TABLE = 'users';

    private const COLUMN = 'username';

    /**
     * The original cache configuration is restored in tearDown(). It is not done automatically by the parent teardown.
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

    public function testModelCacheCheckService_ColdCache_NoneReported(): void
    {
        $this->assertSame([], (new ModelCacheCheckService())->getCacheMismatches());
    }

    public function testModelCacheCheckService_NotYetCachedTablesAreIgnored(): void
    {
        // seed one table; the other uncached tables must be ignored, not reported
        $this->writeCachedSchema(self::TABLE, $this->liveSchema(self::TABLE));

        $this->assertSame([], (new ModelCacheCheckService())->getCacheMismatches());
    }

    public function testModelCacheCheckService_ColumnOnlyInCache(): void
    {
        $schema = $this->liveSchema(self::TABLE);
        $schema->addColumn('zzz_ghost_column', ['type' => 'string', 'null' => true]);
        $this->writeCachedSchema(self::TABLE, $schema);

        $diffs = $this->mismatchDiffsFor(self::TABLE);

        $this->assertContains('column zzz_ghost_column only in cache', $diffs);
    }

    public function testModelCacheCheckService_ColumnOnlyInDb(): void
    {
        $schema = $this->liveSchema(self::TABLE);
        $schema->removeColumn(self::COLUMN);
        $this->writeCachedSchema(self::TABLE, $schema);

        $diffs = $this->mismatchDiffsFor(self::TABLE);

        $this->assertContains('column ' . self::COLUMN . ' only in db', $diffs);
    }

    public function testModelCacheCheckService_ColumnDiffers(): void
    {
        $schema = $this->liveSchema(self::TABLE);
        $schema->addColumn(self::COLUMN, ['type' => 'integer', 'null' => true]);
        $this->writeCachedSchema(self::TABLE, $schema);

        $diffs = $this->mismatchDiffsFor(self::TABLE);

        $this->assertContains('column ' . self::COLUMN . ' differs', $diffs);
    }

    public function testModelCacheCheckService_IndexDifferenceIsReported(): void
    {
        $schema = $this->liveSchema(self::TABLE);
        $schema->addIndex('zzz_ghost_index', ['type' => 'index', 'columns' => [self::COLUMN]]);
        $this->writeCachedSchema(self::TABLE, $schema);

        $diffs = $this->mismatchDiffsFor(self::TABLE);

        $this->assertContains('index zzz_ghost_index only in cache', $diffs);
    }

    public function testModelCacheCheckService_ConstraintDifferenceIsReported(): void
    {
        $schema = $this->liveSchema(self::TABLE);
        $schema->addConstraint('zzz_ghost_unique', ['type' => 'unique', 'columns' => [self::COLUMN]]);
        $this->writeCachedSchema(self::TABLE, $schema);

        $diffs = $this->mismatchDiffsFor(self::TABLE);

        $this->assertContains('constraint zzz_ghost_unique only in cache', $diffs);
    }

    /**
     * Find cache mismatches on the default connection and return the differences for a table.
     *
     * @param string $table Table name.
     * @return array<string>
     */
    private function mismatchDiffsFor(string $table): array
    {
        $mismatches = (new ModelCacheCheckService())->getCacheMismatches();

        $this->assertArrayHasKey($table, $mismatches, 'Expected the table cache to be reported as mismatching.');

        return $mismatches[$table];
    }

    /**
     * Describe a table using a non-caching collection (live database schema).
     *
     * @param string $table Table name.
     * @return \Cake\Database\Schema\TableSchema
     */
    private function liveSchema(string $table): TableSchema
    {
        $connection = ConnectionManager::get(ModelCacheCheckService::DATABASE_CONNECTION_NAME);
        $schema = (new SchemaCollection($connection))->describe($table);
        assert($schema instanceof TableSchema);

        return $schema;
    }

    /**
     * Write a table schema into the model cache
     *
     * @param string $table Table name.
     * @param \Cake\Database\Schema\TableSchema $schema Schema to cache.
     * @return void
     */
    private function writeCachedSchema(string $table, TableSchema $schema): void
    {
        $connection = ConnectionManager::get(ModelCacheCheckService::DATABASE_CONNECTION_NAME);
        $config = $connection->config();
        $keyPrefix = empty($config['cacheKeyPrefix']) ? $connection->configName() : $config['cacheKeyPrefix'];

        Cache::write($keyPrefix . '_' . $table, $schema, ModelCacheCheckService::MODEL_CACHE_CONFIG_KEY);
    }
}
