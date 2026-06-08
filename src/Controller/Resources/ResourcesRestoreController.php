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

namespace App\Controller\Resources;

use App\Controller\AppController;
use App\Error\Exception\ValidationException;
use App\Model\Entity\Resource;
use App\Model\Table\PermissionsTable;
use App\Model\Table\ResourcesTable;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Validation\Validation;
use Passbolt\Folders\Model\Behavior\FolderizableBehavior;
use Passbolt\Metadata\Model\Dto\MetadataResourceDto;
use Passbolt\Metadata\Service\MetadataResourcesRenderService;

/**
 * ResourcesRestoreController Class
 */
class ResourcesRestoreController extends AppController
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
        $this->Resources = $this->fetchTable('Resources');
    }

    /**
     * Resource Restore action.
     *
     * @param string $id The identifier of the resource to restore.
     * @throws \Cake\Http\Exception\NotFoundException If the resource does not exist.
     * @throws \Cake\Http\Exception\ForbiddenException If the user does not have the permission to restore the resource.
     * @throws \Cake\Http\Exception\BadRequestException If the resource id is not a valid uuid.
     * @throws \Cake\Http\Exception\InternalErrorException if the resource could not be restored.
     * @return void
     */
    public function restore(string $id): void
    {
        $this->assertJson();

        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The resource identifier should be a valid UUID.'));
        }

        try {
            $resource = $this->Resources->find()
                ->contain(['ResourceTypes'])
                ->where(['Resources.id' => $id])
                ->firstOrFail();
        } catch (RecordNotFoundException $e) {
            throw new NotFoundException(__('The resource does not exist.'));
        }

        if (!$this->Resources->restore($this->User->id(), $resource)) {
            $this->_handleRestoreError($resource);
            throw new InternalErrorException('Could not restore the resource. Please try again later.');
        }

        $options = [
            'contain' => [
                'creator' => true, 'favorite' => true, 'modifier' => true, 'secret' => true, 'permission' => true,
            ],
        ];
        if (Configure::read('passbolt.plugins.tags.enabled')) {
            $options['contain']['tag'] = true;
        }

        $resource = $this->Resources->findView($this->User->id(), $id, $options)->firstOrFail();
        $resource = FolderizableBehavior::unsetPersonalPropertyIfNull($resource->toArray());
        $resourceDto = MetadataResourceDto::fromArray($resource);
        $resource = (new MetadataResourcesRenderService())->renderResource($resource, $resourceDto->isV5());

        $this->success(__('The resource has been restored successfully.'), $resource);
    }

    /**
     * Manage restore errors.
     *
     * @param \App\Model\Entity\Resource $resource entity
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Http\Exception\ForbiddenException
     * @throws \Cake\Http\Exception\BadRequestException
     * @throws \App\Error\Exception\ValidationException
     * @return void
     */
    protected function _handleRestoreError(Resource $resource): void
    {
        $errors = $resource->getErrors();
        if (empty($errors)) {
            return;
        }
        if (isset($errors['deleted']['is_soft_deleted'])) {
            throw new BadRequestException(__('The resource is not deleted.'));
        }
        if (isset($errors['id']['has_access'])) {
            // If the user has a read access return a 403, otherwise return a 404 to avoid data leak.
            $acoType = PermissionsTable::RESOURCE_ACO;
            if ($this->Resources->Permissions->hasAccess($acoType, $resource->id, $this->User->id())) {
                throw new ForbiddenException(__('You do not have the permission to restore this resource.'));
            }
            throw new NotFoundException(__('The resource does not exist.'));
        }
        if (isset($errors['id']['recoverable_data_exists'])) {
            throw new BadRequestException(__(
                'The resource cannot be restored because its recoverable data is missing.'
            ));
        }
        if (isset($errors['resource_type_id']['resource_type_not_exists'])) {
            throw new BadRequestException(__('Could not restore the resource.'));
        }
        throw new ValidationException(__('Could not restore the resource.'), $resource, $this->Resources);
    }
}
