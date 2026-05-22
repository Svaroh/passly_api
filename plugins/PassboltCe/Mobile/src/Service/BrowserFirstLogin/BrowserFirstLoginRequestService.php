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

namespace Passbolt\Mobile\Service\BrowserFirstLogin;

use App\Error\Exception\ValidationException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Text;
use Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest;
use Passbolt\Mobile\Model\Table\BrowserFirstLoginRequestsTable;

class BrowserFirstLoginRequestService
{
    use LocatorAwareTrait;

    private const SECRET_BYTES = 32;
    private const TTL = '+5 minutes';

    private BrowserFirstLoginRequestsTable $BrowserFirstLoginRequests;

    /**
     * Constructor.
     *
     * @param \Passbolt\Mobile\Model\Table\BrowserFirstLoginRequestsTable|null $table The table.
     */
    public function __construct(?BrowserFirstLoginRequestsTable $table = null)
    {
        $this->BrowserFirstLoginRequests = $table
            ?? $this->fetchTable('Passbolt/Mobile.BrowserFirstLoginRequests');
    }

    /**
     * Create a pairing request.
     *
     * @return array
     */
    public function create(): array
    {
        $secret = bin2hex(random_bytes(self::SECRET_BYTES));
        $request = $this->BrowserFirstLoginRequests->newEntity([
            'id' => Text::uuid(),
            'secret_hash' => $this->hashSecret($secret),
            'status' => BrowserFirstLoginRequest::STATUS_PENDING,
            'expires' => DateTime::now()->modify(self::TTL),
        ], ['accessibleFields' => [
            'id' => true,
            'secret_hash' => true,
            'status' => true,
            'expires' => true,
        ]]);

        if ($request->hasErrors()) {
            throw new ValidationException(__('Could not validate browser first-login request.'), $request);
        }
        if (!$this->BrowserFirstLoginRequests->save($request)) {
            throw new InternalErrorException(__('Could not create browser first-login request.'));
        }

        return [
            'id' => $request->id,
            'secret' => $secret,
            'status' => $request->status,
            'expires' => $request->expires,
        ];
    }

    /**
     * Get a request by id and secret.
     *
     * @param string $id Request id.
     * @param string $secret Request secret.
     * @return \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest
     */
    public function getAuthorized(string $id, string $secret): BrowserFirstLoginRequest
    {
        $request = $this->BrowserFirstLoginRequests->find()
            ->where([
                'id' => $id,
                'secret_hash' => $this->hashSecret($secret),
            ])
            ->first();
        if (!$request) {
            throw new RecordNotFoundException(__('The browser first-login request could not be found.'));
        }
        if ($request->expires->isPast()) {
            throw new UnauthorizedException(__('The browser first-login request has expired.'));
        }

        return $request;
    }

    /**
     * Set the Android-selected account.
     *
     * @param string $id Request id.
     * @param array $data Request data.
     * @return \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest
     */
    public function setAccount(string $id, array $data): BrowserFirstLoginRequest
    {
        $request = $this->getAuthorized($id, $this->getSecret($data));
        $this->assertStatus($request, [BrowserFirstLoginRequest::STATUS_PENDING]);
        $this->assertRequiredString($data, 'user_id');
        $this->assertRequiredString($data, 'user_key_fingerprint');

        return $this->saveFields($request, [
            'status' => BrowserFirstLoginRequest::STATUS_ACCOUNT_SELECTED,
            'user_id' => $data['user_id'],
            'user_key_fingerprint' => strtoupper($data['user_key_fingerprint']),
        ]);
    }

    /**
     * Set the encrypted GPGAuth challenge from the browser.
     *
     * @param string $id Request id.
     * @param array $data Request data.
     * @return \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest
     */
    public function setChallenge(string $id, array $data): BrowserFirstLoginRequest
    {
        $request = $this->getAuthorized($id, $this->getSecret($data));
        $this->assertStatus($request, [BrowserFirstLoginRequest::STATUS_ACCOUNT_SELECTED]);
        $this->assertRequiredString($data, 'encrypted_user_auth_token');

        return $this->saveFields($request, [
            'status' => BrowserFirstLoginRequest::STATUS_CHALLENGE_READY,
            'encrypted_user_auth_token' => $data['encrypted_user_auth_token'],
        ]);
    }

    /**
     * Set the Android decrypted one-time login token result.
     *
     * @param string $id Request id.
     * @param array $data Request data.
     * @return \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest
     */
    public function setResponse(string $id, array $data): BrowserFirstLoginRequest
    {
        $request = $this->getAuthorized($id, $this->getSecret($data));
        $this->assertStatus($request, [BrowserFirstLoginRequest::STATUS_CHALLENGE_READY]);
        $this->assertRequiredString($data, 'user_token_result');

        return $this->saveFields($request, [
            'status' => BrowserFirstLoginRequest::STATUS_RESPONSE_READY,
            'user_token_result' => $data['user_token_result'],
        ]);
    }

    /**
     * Mark a pairing request as complete.
     *
     * @param string $id Request id.
     * @param array $data Request data.
     * @return \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest
     */
    public function complete(string $id, array $data): BrowserFirstLoginRequest
    {
        $request = $this->getAuthorized($id, $this->getSecret($data));
        $this->assertStatus($request, [BrowserFirstLoginRequest::STATUS_RESPONSE_READY]);

        return $this->saveFields($request, [
            'status' => BrowserFirstLoginRequest::STATUS_COMPLETE,
        ]);
    }

    /**
     * Save request fields.
     *
     * @param \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest $request Request.
     * @param array $fields Fields.
     * @return \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest
     */
    private function saveFields(BrowserFirstLoginRequest $request, array $fields): BrowserFirstLoginRequest
    {
        $request = $this->BrowserFirstLoginRequests->patchEntity($request, $fields, [
            'accessibleFields' => array_fill_keys(array_keys($fields), true),
        ]);
        if ($request->hasErrors()) {
            throw new ValidationException(__('Could not validate browser first-login request.'), $request);
        }
        if (!$this->BrowserFirstLoginRequests->save($request)) {
            throw new InternalErrorException(__('Could not update browser first-login request.'));
        }

        return $request;
    }

    /**
     * Assert expected status.
     *
     * @param \Passbolt\Mobile\Model\Entity\BrowserFirstLoginRequest $request Request.
     * @param array $allowed Allowed statuses.
     * @return void
     */
    private function assertStatus(BrowserFirstLoginRequest $request, array $allowed): void
    {
        if (!in_array($request->status, $allowed, true)) {
            throw new BadRequestException(__('The browser first-login request is not in the expected state.'));
        }
    }

    /**
     * Get request secret from request data.
     *
     * @param array $data Request data.
     * @return string
     */
    private function getSecret(array $data): string
    {
        $this->assertRequiredString($data, 'secret');

        return $data['secret'];
    }

    /**
     * Assert a required string field.
     *
     * @param array $data Request data.
     * @param string $field Field name.
     * @return void
     */
    private function assertRequiredString(array $data, string $field): void
    {
        if (empty($data[$field]) || !is_string($data[$field])) {
            throw new BadRequestException(__('The field {0} is required.', $field));
        }
    }

    /**
     * Hash a pairing secret for storage.
     *
     * @param string $secret Secret.
     * @return string
     */
    private function hashSecret(string $secret): string
    {
        return hash('sha256', $secret);
    }
}
