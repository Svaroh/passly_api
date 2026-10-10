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
 * @since         5.14.0
 */
namespace Passbolt\TestData\Command\Large;

use App\Utility\UuidFactory;
use Cake\Core\Configure;
use Passbolt\TestData\Lib\DataCommand;

class EmailQueueDataCommand extends DataCommand
{
    public string $entityName = 'EmailQueue';

    /**
     * Get email_queue data
     *
     * @return array
     */
    public function getData(): array
    {
        $emailQueue = [];

        $max = Configure::read('PassboltTestData.scenarios.large.install.count.email_queue');
        for ($i = 0; $i < $max; $i++) {
            $email = 'user_' . $i . '@passbolt.com';
            $title = 'Test subject ' . $i;
            $locale = 'en-UK';
            $fullBaseUrl = '/';
            $body = compact('fullBaseUrl');

            $emailQueue[] = [
                'email' => $email,
                'subject' => $title,
                'config' => 'default',
                'template' => 'LU/group_user_delete',
                'layout' => 'default',
                'template_vars' => json_encode(compact('email', 'title', 'locale', 'fullBaseUrl', 'body')),
                'theme' => '',
                'format' => 'html',
                'sent' => 0,
                'locked' => 0,
                'send_tries' => 0,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
                'created_by' => UuidFactory::uuid('user.id.admin'),
                'modified_by' => UuidFactory::uuid('user.id.admin'),
            ];
        }

        return $emailQueue;
    }
}
