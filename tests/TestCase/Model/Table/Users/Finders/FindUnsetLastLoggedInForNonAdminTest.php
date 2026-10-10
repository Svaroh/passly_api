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
 * @since         5.15.0
 */

namespace App\Test\TestCase\Model\Table\Users\Finders;

use App\Model\Entity\Role;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCase;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;

class FindUnsetLastLoggedInForNonAdminTest extends AppTestCase
{
    /**
     * @var UsersTable
     */
    private $usersTable;
    /**
     * @var RolesTable
     */
    private $rolesTable;

    public function setUp(): void
    {
        parent::setUp();
        $this->usersTable = TableRegistry::getTableLocator()->get('Users');
        $this->rolesTable = TableRegistry::getTableLocator()->get('Roles');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->usersTable);
        unset($this->rolesTable);
    }

    /**
     * @return void
     */
    public function testFindUnsetLastLoggedInForNonAdmin_Success_ShowFieldAsUserIsAdmin(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->usersTable->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: true)->first();

        $this->assertObjectHasAttribute('last_logged_in', $result);
    }

    /**
     * @return void
     */
    public function testFindUnsetLastLoggedInForNonAdmin_Success_HideFieldAsUserIsNotAdmin(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();

        $result = $this->usersTable->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: false)->first();

        $this->assertObjectNotHasAttribute('last_logged_in', $result);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_BelongsToAssociationsAreLoaded(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->usersTable->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: false)->contain('Roles')->first();

        $this->assertObjectHasAttribute('role', $result);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_BelongsToManyAssociationsAreLoaded(): void
    {
        $user = UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        GroupFactory::make()->withGroupsManagersFor([$user])->persist();
        $result = $this->usersTable->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: false)->contain('GroupsUsers')->first();

        $this->assertObjectHasAttribute('groups_users', $result);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_HasOneAssociationsAreLoaded(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->usersTable->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: false)->contain('Profiles')->first();

        $this->assertObjectHasAttribute('profile', $result);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_HasManyAssociationsAreLoaded(): void
    {
        $user = UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        AuthenticationTokenFactory::make()->userId($user->id)->persist();
        $result = $this->usersTable->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: false)->contain('AuthenticationTokens')->first();

        $this->assertObjectHasAttribute('authentication_tokens', $result);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_ShowFieldAsUserIsAdminWhenUserIsContained(): void
    {
        $role = RoleFactory::make()->admin()->persist();
        UserFactory::make()->admin()->withRole($role->name)->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->rolesTable->find()->contain(['Users' => function (SelectQuery $q) {
            return $q->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: true);
        }])->first();

        $this->assertObjectHasAttribute('last_logged_in', $result->get('users')[0]);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_HideFieldAsUserIsNonAdminWhenUserIsContained(): void
    {
        $role = RoleFactory::make()->user()->persist();
        UserFactory::make()->user()->withRole($role->name)->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->rolesTable->find()->contain(['Users' => function (SelectQuery $q) {
            return $q->find('unsetLastLoggedInForNonAdmin', showLastLoggedIn: false);
        }])->first();

        $this->assertObjectNotHasAttribute('last_logged_in', $result->get('users')[0]);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_HideFieldAsTheUserFindDoNotHaveRoleToCheck(): void
    {
        $role = RoleFactory::make()->admin()->persist();
        UserFactory::make()->admin()->withRole($role->name)->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->rolesTable->find()->contain('Users')->first();

        $this->assertObjectNotHasAttribute('last_logged_in', $result->get('users')[0]);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_HideFieldAsTheUserFindDoNotHaveRoleToCheckWhenPassingRoleToRoleFinder(): void
    {
        $role = RoleFactory::make()->admin()->persist();
        UserFactory::make()->admin()->withRole($role->name)->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->rolesTable->find(role: Role::ADMIN)->contain('Users')->first();

        $this->assertObjectNotHasAttribute('last_logged_in', $result->get('users')[0]);
    }

    public function testFindUnsetLastLoggedInForNonAdmin_Success_HideFieldAsTheUserFindDoNotHaveRoleToCheckWhenTryingToSelectTheField(): void
    {
        $role = RoleFactory::make()->admin()->persist();
        UserFactory::make()->admin()->withRole($role->name)->active()->lastLoggedIn(DateTime::now()->subDays(3))->persist();
        $result = $this->rolesTable->find()->contain([
            'Users' => [
                'fields' => ['last_logged_in'],
            ],
        ])->first();

        $this->assertObjectNotHasAttribute('last_logged_in', $result->get('users')[0]);
    }
}
