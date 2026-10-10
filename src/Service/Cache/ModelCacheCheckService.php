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
namespace App\Service\Cache;

use Cake\Cache\Cache;
use Cake\Core\Exception\CakeException;
use Cake\Database\Connection;
use Cake\Database\Schema\Collection as SchemaCollection;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\ConnectionManager;

class ModelCacheCheckService
{
    public const MODEL_CACHE_CONFIG_KEY = '_cake_model_';

    public const DATABASE_CONNECTION_NAME = 'default';

    /**
     * Find the tables whose cached schema does not match the live database schema.
     *
     * @param string $datasource Connection name to check.
     * @return array<string, array<string>> Mismatching tables with the list of human-readable differences
     * @throws \Cake\Core\Exception\CakeException When the database connection is not found
     */
    public function getCacheMismatches(string $datasource = self::DATABASE_CONNECTION_NAME): array
    {
        $connection = ConnectionManager::get($datasource);
        if (!$connection instanceof Connection) {
            throw new CakeException('No database connection found for the label: ' . $datasource);
        }

        // Non-caching collection: reading from it never creates or refreshes the cache.
        $liveSchema = new SchemaCollection($connection);

        // Cache entries are keyed "<cacheKeyPrefix>_<table>"; cacheKeyPrefix defaults to the connection name.
        $connectionConfig = $connection->config();
        $keyPrefix = empty($connectionConfig['cacheKeyPrefix'])
            ? $connection->configName()
            : (string)$connectionConfig['cacheKeyPrefix'];

        $mismatches = [];
        foreach ($liveSchema->listTables() as $table) {
            $cached = $this->readCachedSchema($keyPrefix, $table);
            if ($cached === null) {
                continue;
            }

            $differences = $this->describeMismatch($cached, $liveSchema->describe($table));
            if ($differences !== []) {
                $mismatches[$table] = $differences;
            }
        }

        return $mismatches;
    }

    /**
     * Read the cached schema for a table without any write side effect (Cache::read() never repopulates on a miss).
     *
     * @param string $keyPrefix The connection cache key prefix.
     * @param string $table Table name.
     * @return \Cake\Database\Schema\TableSchemaInterface|null
     */
    private function readCachedSchema(string $keyPrefix, string $table): ?TableSchemaInterface
    {
        $cacheKey = $keyPrefix . '_' . $table;
        $cached = Cache::read($cacheKey, self::MODEL_CACHE_CONFIG_KEY);

        return $cached instanceof TableSchemaInterface ? $cached : null;
    }

    /**
     * Build an order-independent representation of a table schema for loose (==) comparison.
     *
     * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema.
     * @return array
     */
    private function fingerprint(TableSchemaInterface $schema): array
    {
        $data = ['columns' => [], 'indexes' => [], 'constraints' => []];

        foreach ($schema->columns() as $column) {
            $data['columns'][$column] = $schema->getColumn($column);
        }
        foreach ($schema->indexes() as $index) {
            $data['indexes'][$index] = $schema->getIndex($index);
        }
        foreach ($schema->constraints() as $constraint) {
            $data['constraints'][$constraint] = $schema->getConstraint($constraint);
        }

        return $data;
    }

    /**
     * Build a human-readable list of differences (columns, indexes, constraints), one line per difference.
     *
     * @param \Cake\Database\Schema\TableSchemaInterface $cached Cached schema.
     * @param \Cake\Database\Schema\TableSchemaInterface $live Live schema.
     * @return array<string>
     */
    private function describeMismatch(TableSchemaInterface $cached, TableSchemaInterface $live): array
    {
        $cachedFp = $this->fingerprint($cached);
        $liveFp = $this->fingerprint($live);

        $parts = [];
        foreach (['columns' => 'column', 'indexes' => 'index', 'constraints' => 'constraint'] as $section => $noun) {
            $cachedItems = $cachedFp[$section];
            $liveItems = $liveFp[$section];

            foreach (array_diff_key($liveItems, $cachedItems) as $name => $_) {
                $parts[] = "{$noun} {$name} only in db";
            }
            foreach (array_diff_key($cachedItems, $liveItems) as $name => $_) {
                $parts[] = "{$noun} {$name} only in cache";
            }
            foreach (array_intersect_key($liveItems, $cachedItems) as $name => $liveItem) {
                // Definitions are intentionally compared by value (loose !=), not by strict identity: only genuinely
                // differing definitions are reported as a discrepancy.
                if ($liveItem != $cachedItems[$name]) {
                    $parts[] = "{$noun} {$name} differs";
                }
            }
        }

        return $parts;
    }
}
