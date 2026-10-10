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

namespace App\Test\TestCase\Model\Table\Users;

use App\Model\Entity\Role;
use App\Model\Table\UsersTable;
use App\Test\Factory\UserFactory;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * @covers \App\Model\Table\UsersTable
 */
class UsersTableBeforeFindTest extends TestCase
{
    /**
     * @var \App\Model\Table\UsersTable
     */
    public UsersTable|Table $Users;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($this->Users);
        parent::tearDown();
    }

    /**
     * Last logged in data should be excluded using beforeFind.
     *
     * @covers \App\Model\Traits\Users\UsersFindersTrait
     * @return void
     */
    public function testUsersTableBeforeFind_ExcludeLastLoggedInField(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make()->user()->inactive()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make()->user()->disabled()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make()->user()->deleted()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make(['last_logged_in' => DateTime::now()->subDays(2)])
            ->admin()
            ->active()
            ->persist();

        $users = $this->Users->find();

        /** @var \App\Model\Entity\User $user */
        foreach ($users->all()->toArray() as $user) {
            $this->assertFalse($user->has('last_logged_in'));
        }
    }

    /**
     * Last logged in data should be excluded using beforeFind.
     *
     * @covers \App\Model\Traits\Users\UsersFindersTrait
     * @return void
     */
    public function testUsersTableBeforeFind_NotExcludeLastLoggedInField(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make()->user()->inactive()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make()->user()->disabled()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make()->user()->deleted()->lastLoggedIn(DateTime::now())->persist();
        $admin = UserFactory::make(['last_logged_in' => DateTime::now()->subDays(2)])
            ->admin()
            ->active()
            ->persist();

        $users = $this->Users->find('all', showLastLoggedIn: $admin->get('role')['name'] === Role::ADMIN);

        /** @var \App\Model\Entity\User $user */
        foreach ($users->all()->toArray() as $user) {
            $this->assertTrue($user->has('last_logged_in'));
        }
    }

    /**
     * Last logged in data should be present using findAuthIdentifier.
     *
     * @covers \App\Model\Traits\Users\UsersFindersTrait
     * @return void
     */
    public function testUsersTableFindAuthIdentifier_NotExcludeLastLoggedInField(): void
    {
        UserFactory::make()->user()->active()->lastLoggedIn(DateTime::now())->persist();
        UserFactory::make(['last_logged_in' => DateTime::now()->subDays(2)])
            ->admin()
            ->active()
            ->persist();

        $users = $this->Users->find('authIdentifier');

        /** @var \App\Model\Entity\User $user */
        foreach ($users->all()->toArray() as $user) {
            $this->assertTrue($user->has('last_logged_in'));
        }
    }
}
