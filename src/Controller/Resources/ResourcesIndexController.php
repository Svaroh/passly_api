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
 * @since         2.0.0
 */

namespace App\Controller\Resources;

use App\Controller\AppController;
use App\Database\Type\ISOFormatDateTimeType;
use App\Model\Table\ResourcesTable;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Utility\Hash;
use Exception;
use Passbolt\Folders\Model\Behavior\FolderizableBehavior;
use Passbolt\Metadata\Service\MetadataResourcesRenderService;

/**
 * @property \BryanCrowe\ApiPagination\Controller\Component\ApiPaginationComponent $ApiPagination
 */
class ResourcesIndexController extends AppController
{
    /**
     * @var \App\Model\Table\ResourcesTable
     */
    protected ResourcesTable $Resources;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('ApiPagination', [
            'model' => 'Resources',
        ]);
        $this->Resources = $this->fetchTable('Resources');
    }

    public array $paginate = [
        'sortableFields' => [
            'Resources.name',
            'Resources.username',
            'Resources.uri',
            'Resources.modified',
        ],
        'order' => [
            'Resources.name' => 'asc', // Default sorted field
        ],
    ];

    /**
     * Resource Index action
     *
     * @return void
     */
    public function index()
    {
        $this->assertJson();

        // Retrieve and sanity the query options.
        $whitelist = [
            'contain' => [
                'creator', 'favorite', 'modifier', 'secret', 'resource-type',
                'permission', 'permissions', 'permissions.user.profile', 'permissions.group',
            ],
            'filter' => [
                'is-favorite', 'is-shared-with-group', 'is-owned-by-me',
                'is-shared-with-me', 'has-id', 'is-deleted', 'metadata_key_type',
            ],
        ];

        if (Configure::read('passbolt.plugins.tags')) {
            $whitelist['contain'][] = 'tag'; // @deprecate should be tags
            $whitelist['filter'][] = 'has-tag';
        }
        if (Configure::read('passbolt.plugins.folders')) {
            $whitelist['filter'][] = 'has-parent';
        }
        $options = $this->QueryString->get($whitelist);

        // Performance improvement: map query result datetime properties to string.
        ISOFormatDateTimeType::mapDatetimeTypesToMe();
        $resources = $this->Resources->findIndex($this->User->id(), $options)->disableHydration();
        $resources = $this->paginate($resources)->items();
        /** @psalm-suppress InvalidArgument **/
        $resources = FolderizableBehavior::unsetPersonalPropertyIfNullOnResultSet($resources);
        ISOFormatDateTimeType::remapDatetimeTypesToDefault();
        $this->_logSecretAccesses($resources, $options);
        $resources = (new MetadataResourcesRenderService())->renderResources($resources->toArray());
        $this->success(__('The operation was successful.'), $resources);
    }

    /**
     * Log secrets accesses in secretAccesses table.
     *
     * @param \Cake\Collection\CollectionInterface $resources resources
     * @param array $queryOptions The query options
     * @return void
     */
    protected function _logSecretAccesses(CollectionInterface $resources, array $queryOptions)
    {
        $containSecret = (bool)Hash::get($queryOptions, 'contain.secret');
        if (!$containSecret) {
            return;
        }

        if (!$this->Resources->getAssociation('Secrets')->hasAssociation('SecretAccesses')) {
            return;
        }

        // Failing to write an audit row must not deny the index itself. This endpoint serves whole pages of
        // resources, so a single failing insert would otherwise take down listing and synchronisation for every
        // client, repeatedly, until the cause is fixed by hand.
        // The deliberate single secret reads keep throwing - see SecretsViewController.
        $failureCount = 0;
        $firstError = null;

        foreach ($resources as $resource) {
            $secrets = Hash::get($resource, 'secrets');
            if (!isset($secrets)) {
                continue;
            }

            foreach ($secrets as $secret) {
                try {
                    $this->Resources->Secrets->SecretAccesses->createFromSecretDetails(
                        $this->User->getAccessControl(),
                        Hash::get($secret, 'resource_id'),
                        Hash::get($secret, 'id'),
                    );
                } catch (Exception $e) {
                    $failureCount++;
                    $firstError = $firstError ?? $e->getMessage();
                }
            }
        }

        // Summarised once per request on purpose: a broken audit table on a page of a few thousand resources
        // would otherwise write one line per secret, on every synchronisation, and drown the error log.
        if ($failureCount > 0) {
            Log::error(sprintf(
                'Could not log %d secret access entries for user %s. First error: %s',
                $failureCount,
                $this->User->id(),
                $firstError
            ));
        }
    }
}
