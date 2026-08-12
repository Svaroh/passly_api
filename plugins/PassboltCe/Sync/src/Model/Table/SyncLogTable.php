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

namespace Passbolt\Sync\Model\Table;

use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Change journal for the sync/1 delta protocol.
 *
 * @method \Passbolt\Sync\Model\Entity\SyncLog newEmptyEntity()
 * @method \Passbolt\Sync\Model\Entity\SyncLog newEntity(array $data, array $options = [])
 * @method \Passbolt\Sync\Model\Entity\SyncLog get(mixed $primaryKey, array $options = [])
 */
class SyncLogTable extends Table
{
    public const ENTITY_TYPE_RESOURCE = 'resource';
    public const ENTITY_TYPE_FOLDER = 'folder';
    public const ENTITY_TYPE_SECRET = 'secret';
    public const ENTITY_TYPE_USER = 'user';
    public const ENTITY_TYPE_GROUP = 'group';
    public const ENTITY_TYPE_TAG = 'tag';
    public const ENTITY_TYPE_RESOURCE_TYPE = 'resource_type';
    public const ENTITY_TYPE_METADATA_KEY = 'metadata_key';

    public const OP_UPSERT = 'upsert';
    public const OP_DELETE = 'delete';

    /**
     * Entity types a client can be told about.
     *
     * Permission and group membership changes are not among them on purpose: they are translated into events on the
     * resource or folder whose visibility they change, because that is what the client has to react to.
     *
     * @var array<string>
     */
    public const ENTITY_TYPES = [
        self::ENTITY_TYPE_RESOURCE,
        self::ENTITY_TYPE_FOLDER,
        self::ENTITY_TYPE_SECRET,
        self::ENTITY_TYPE_USER,
        self::ENTITY_TYPE_GROUP,
        self::ENTITY_TYPE_TAG,
        self::ENTITY_TYPE_RESOURCE_TYPE,
        self::ENTITY_TYPE_METADATA_KEY,
    ];

    /**
     * @var array<string>
     */
    public const OPS = [
        self::OP_UPSERT,
        self::OP_DELETE,
    ];

    /**
     * A transaction with a lower seq can commit after a higher one, so a reader that took MAX(seq) at that moment
     * would skip the laggard forever. Events younger than this are withheld to let in flight writes land.
     */
    public const WATERMARK_LAG_SECONDS = 2;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('sync_log');
        $this->setDisplayField('seq');
        $this->setPrimaryKey('seq');
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('entity_type', 'create', __('An entity type is required.'))
            ->inList('entity_type', self::ENTITY_TYPES, __('The entity type is not supported.'));

        $validator
            ->uuid('entity_id', __('The entity identifier should be a valid UUID.'))
            ->requirePresence('entity_id', 'create', __('An entity identifier is required.'));

        $validator
            ->requirePresence('op', 'create', __('An operation is required.'))
            ->inList('op', self::OPS, __('The operation is not supported.'));

        $validator
            ->dateTime('created')
            ->requirePresence('created', 'create', __('A creation date is required.'));

        return $validator;
    }

    /**
     * Highest sequence number that is safe to hand out as a cursor.
     *
     * @return int
     */
    public function findWatermark(): int
    {
        $watermark = $this->find()
            ->select(['max_seq' => $this->find()->func()->max('seq')])
            ->where(['created <=' => $this->watermarkThreshold()])
            ->first();

        return (int)($watermark->get('max_seq') ?? 0);
    }

    /**
     * Oldest sequence number still retained. A client whose cursor is below it cannot be served a delta and has to
     * bootstrap again.
     *
     * @return int
     */
    public function findOldestRetainedSequence(): int
    {
        $oldest = $this->find()
            ->select(['min_seq' => $this->find()->func()->min('seq')])
            ->first();

        return (int)($oldest->get('min_seq') ?? 0);
    }

    /**
     * @return \Cake\I18n\DateTime
     */
    protected function watermarkThreshold(): DateTime
    {
        return DateTime::now()->subSeconds(self::WATERMARK_LAG_SECONDS);
    }
}
