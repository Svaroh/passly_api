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
 * @since         2.13.0
 */

namespace Passbolt\Folders\Test\TestCase\Controller\Folders;

use App\Model\Entity\Permission;
use App\Test\Factory\UserFactory;
use App\Test\Lib\Model\PermissionsModelTrait;
use App\Utility\UuidFactory;
use Cake\Utility\Hash;
use Passbolt\Folders\Test\Factory\FolderFactory;
use Passbolt\Folders\Test\Lib\FoldersIntegrationTestCase;
use Passbolt\Folders\Test\Lib\Model\FoldersModelTrait;
use Passbolt\Folders\Test\Lib\Model\FoldersRelationsModelTrait;

/**
 * Passbolt\Folders\Controller\Folders\FoldersCreateController Test Case
 *
 * @uses \Passbolt\Folders\Controller\Folders\FoldersCreateController
 */
class FoldersCreateControllerTest extends FoldersIntegrationTestCase
{
    use FoldersModelTrait;
    use FoldersRelationsModelTrait;
    use PermissionsModelTrait;

    public function testFoldersCreateFolder_PersoSuccess1_CreateFolder()
    {
        $userI = UserFactory::make()->user()->persist();
        $data = ['name' => 'A'];
        $this->logInAs($userI);
        $this->postJson('/folders.json?contain[creator]=1&contain[modifier]=1&api-version=2', $data);
        $this->assertSuccess();

        // Assert controller response
        $folder = $this->_responseJsonBody;
        $this->assertFolderAttributes($folder);
        $this->assertEquals($data['name'], $folder->name);
        $this->assertEquals($userI->get('id'), $folder->created_by);
        $this->assertEquals($userI->get('id'), $folder->modified_by);
        $this->assertObjectNotHasAttribute('last_logged_in', $folder->creator);
        $this->assertObjectNotHasAttribute('last_logged_in', $folder->modifier);
    }

    public function testFoldersCreateFolder_PersoSuccess2_CreateInFolder()
    {
        $userI = UserFactory::make()->user()->persist();
        $folderA = FolderFactory::make()->withPermissionsFor([$userI])->persist();

        $data = [
            'name' => 'B',
            'folder_parent_id' => $folderA->get('id'),
        ];
        $this->logInAs($userI);
        $this->postJson('/folders.json?api-version=2', $data);
        $this->assertSuccess();

        // Assert controller response
        $folder = $this->_responseJsonBody;
        $this->assertFolderAttributes($folder);
        $this->assertEquals($data['name'], $folder->name);
        $this->assertEquals($data['folder_parent_id'], $folder->folder_parent_id);
        $this->assertEquals($userI->get('id'), $folder->created_by);
        $this->assertEquals($userI->get('id'), $folder->modified_by);
    }

    public function testFoldersCreateFolder_CommonError1_ValidationError()
    {
        $data = ['name' => ''];
        $this->logInAsUser();
        $this->postJson('/folders.json?api-version=2', $data);
        $this->assertError(400, 'Could not validate folder data.');
        $arr = $this->getResponseBodyAsArray();
        $error = Hash::get($arr, 'name');
        $this->assertNotNull($error, 'The test should return an error of the given field.');
        $this->assertEquals('The name should not be empty.', $error['_empty']);
    }

    public function testFoldersCreateFolder_CommonError2_ParentFolderDoesNotExist()
    {
        $data = [
            'name' => 'B',
            'folder_parent_id' => UuidFactory::uuid('folder.id.not-exist'),
        ];
        $this->logInAsUser();
        $this->postJson('/folders.json?api-version=2', $data);
        $this->assertError(400, 'Could not validate folder data');
        $arr = $this->getResponseBodyAsArray();
        $error = Hash::get($arr, 'folder_parent_id');
        $this->assertNotNull($error, 'The test should return an error of the given field.');
        $this->assertEquals('The folder parent must exist.', $error['folder_exists']);
    }

    public function testFoldersCreateFolder_SharedError1_ParentFolderInsufficientPermission()
    {
        [$userA, $userB] = UserFactory::make(2)->user()->persist();
        $folderA = FolderFactory::make()
            ->withPermissionsFor([$userA], Permission::READ)
            ->withPermissionsFor([$userB])
            ->persist();

        $data = [
            'name' => 'B',
            'folder_parent_id' => $folderA->get('id'),
        ];
        $this->logInAs($userA);
        $this->postJson('/folders.json?api-version=2', $data);
        $arr = $this->getResponseBodyAsArray();
        $error = Hash::get($arr, 'folder_parent_id');
        $this->assertNotNull($error, 'The test should return an error of the given field.');
        $this->assertEquals('You are not allowed to create content into the parent folder.', $error['has_folder_access']);
    }

    public function testFoldersCreateFolderError_IsProtectedByCsrfToken()
    {
        $this->disableCsrfToken();
        $this->logInAsUser();
        $this->post('/folders.json?api-version=2');
        $this->assertResponseCode(403);
    }

    public function testFoldersCreateFolderError_NotAuthenticated()
    {
        $this->postJson('/folders.json?api-version=2');
        $this->assertAuthenticationError();
    }

    public function testFoldersCreateError_NotJson()
    {
        $this->logInAsUser();
        $this->post('/folders');
        $this->assertResponseCode(404);
    }
}
