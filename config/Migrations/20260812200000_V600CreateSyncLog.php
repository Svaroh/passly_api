<?php
declare(strict_types=1);
// @codingStandardsIgnoreStart
use Migrations\AbstractMigration;

/**
 * Change journal backing the sync/1 delta protocol, see docs/sync-protocol.md.
 *
 * One row per change event, no payload: the payload is resolved at read time so that access control is always
 * evaluated against current visibility rather than against whatever was true when the event was recorded.
 *
 * Deliberately portable rather than MySQL flavoured: entity_type and op are plain strings validated in the table
 * class instead of ENUM columns, and seq is a plain auto incrementing big integer, so the same schema works on
 * MySQL, MariaDB and PostgreSQL.
 */
class V600CreateSyncLog extends AbstractMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        $this
            ->table('sync_log', ['id' => false, 'primary_key' => ['seq'], 'collation' => 'utf8mb4_unicode_ci'])
            // the client cursor: monotonic, so clock skew and equal timestamps can never lose an event
            ->addColumn('seq', 'biginteger', [
                'default' => null,
                'null' => false,
                'signed' => false,
                'identity' => true,
            ])
            ->addColumn('entity_type', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('entity_id', 'uuid', [
                'default' => null,
                'null' => false,
                'encoding' => 'ascii',
                'collation' => 'ascii_general_ci',
            ])
            ->addColumn('op', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            // only used for retention pruning and for the watermark; the cursor is seq, never this
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['entity_type', 'entity_id'])
            ->addIndex(['created'])
            ->create();
    }
}
