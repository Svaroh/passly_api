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
 */

namespace Passbolt\Mobile\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest;

class BrowserFirstLoginRequestsTable extends Table
{
    /**
     * Initialize method.
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('browser_first_login_requests');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id', __('The browser first-login request id should be a uuid.'))
            ->allowEmptyString('id', null, 'create');

        $validator
            ->ascii('secret_hash', __('The secret hash should be an ascii string.'))
            ->lengthBetween('secret_hash', [64, 64], __('The secret hash should be 64 characters.'))
            ->requirePresence('secret_hash', 'create', __('A secret hash is required.'));

        $validator
            ->notEmptyString('status', __('The status should not be empty.'))
            ->requirePresence('status', true, __('The status is required.'))
            ->inList('status', BrowserFirstLoginRequest::STATUSES, __('The status is invalid.'));

        $validator
            ->uuid('user_id', __('The user id should be a uuid.'))
            ->allowEmptyString('user_id');

        $validator
            ->ascii('user_key_fingerprint', __('The user key fingerprint should be an ascii string.'))
            ->lengthBetween('user_key_fingerprint', [40, 40], __('The user key fingerprint should be 40 characters.'))
            ->allowEmptyString('user_key_fingerprint');

        $validator
            ->dateTime('expires', ['ymd'], __('The expiry date should be a valid date.'))
            ->requirePresence('expires', 'create', __('An expiry date is required.'));

        $validator
            ->scalar('encrypted_private_key', __('The encrypted private key should be a scalar value.'))
            ->allowEmptyString('encrypted_private_key');

        return $validator;
    }
}
