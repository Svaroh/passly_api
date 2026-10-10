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
namespace App\Test\Factory;

use Cake\I18n\DateTime;
use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Faker\Generator;

/**
 * SessionFactory
 */
class SessionFactory extends CakephpBaseFactory
{
    /*
     * Simulate session.gc_maxlifetime
     */
    public const TIMEOUT = 1440; // In seconds (24 minutes)

    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'Sessions';
    }

    /**
     * Defines the factory's default values. This is useful for
     * not nullable fields. You may use methods of the present factory here too.
     *
     * @return void
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            $randomDate = DateTime::now()->subDays(rand(0, 4));

            return [
                'id' => $faker->uuid(),
                'data' => $faker->text(),
                'created' => $randomDate,
                'modified' => $randomDate,
                'expires' => $randomDate->getTimestamp() + self::TIMEOUT,
            ];
        });
    }

    /**
     * Set modified and then expires based on modified.
     */
    public function modifiedAt(DateTime $when): self
    {
        return $this->patchData([
            'modified' => $when,
            'expires' => $when->getTimestamp() + self::TIMEOUT,
        ]);
    }

    /**
     * Create fresh sessions.
     */
    public function modifiedNow(): self
    {
        return $this->modifiedAt(DateTime::now());
    }
}
