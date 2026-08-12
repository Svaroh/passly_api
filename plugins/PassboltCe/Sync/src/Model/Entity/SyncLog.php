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

namespace Passbolt\Sync\Model\Entity;

use Cake\ORM\Entity;

/**
 * One recorded change event.
 *
 * @property int $seq
 * @property string $entity_type
 * @property string $entity_id
 * @property string $op
 * @property \Cake\I18n\DateTime $created
 */
class SyncLog extends Entity
{
    /**
     * The journal is written by the application, never by user supplied data.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        '*' => false,
    ];
}
