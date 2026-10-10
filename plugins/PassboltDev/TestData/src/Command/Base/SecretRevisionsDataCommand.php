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
 * @license       https://opensource.org/licenses/AGPL-3.null AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         5.14.0
 */
namespace Passbolt\TestData\Command\Base;

use App\Utility\UuidFactory;
use Passbolt\TestData\Lib\DataCommand;

class SecretRevisionsDataCommand extends DataCommand
{
    public string $entityName = 'SecretRevisions';

    /**
     * Get the secret revisions data
     *
     * @return array
     */
    public function getData(): array
    {
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.apache'),
            'resource_id' => UuidFactory::uuid('resource.id.apache'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 days')),
            'created_by' => UuidFactory::uuid('user.id.ada'),
            'modified_by' => UuidFactory::uuid('user.id.ada'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.april'),
            'resource_id' => UuidFactory::uuid('resource.id.april'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 days')),
            'created_by' => UuidFactory::uuid('user.id.betty'),
            'modified_by' => UuidFactory::uuid('user.id.betty'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.bower'),
            'resource_id' => UuidFactory::uuid('resource.id.bower'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 years')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 years')),
            'created_by' => UuidFactory::uuid('user.id.carol'),
            'modified_by' => UuidFactory::uuid('user.id.carol'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.cakephp'),
            'resource_id' => UuidFactory::uuid('resource.id.cakephp'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 hours')),
            'created_by' => UuidFactory::uuid('user.id.ada'),
            'modified_by' => UuidFactory::uuid('user.id.ada'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.canjs'),
            'resource_id' => UuidFactory::uuid('resource.id.canjs'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 weeks')),
            'created_by' => UuidFactory::uuid('user.id.edith'),
            'modified_by' => UuidFactory::uuid('user.id.edith'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.centos'),
            'resource_id' => UuidFactory::uuid('resource.id.centos'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 months')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 months')),
            'created_by' => UuidFactory::uuid('user.id.dame'),
            'modified_by' => UuidFactory::uuid('user.id.dame'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.chai'),
            'resource_id' => UuidFactory::uuid('resource.id.chai'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 months')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 months')),
            'created_by' => UuidFactory::uuid('user.id.betty'),
            'modified_by' => UuidFactory::uuid('user.id.betty'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.composer'),
            'resource_id' => UuidFactory::uuid('resource.id.composer'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.carol'),
            'modified_by' => UuidFactory::uuid('user.id.carol'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.debian'),
            'resource_id' => UuidFactory::uuid('resource.id.debian'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.dame'),
            'modified_by' => UuidFactory::uuid('user.id.dame'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.docker'),
            'resource_id' => UuidFactory::uuid('resource.id.docker'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.edith'),
            'modified_by' => UuidFactory::uuid('user.id.edith'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.enlightenment'),
            'resource_id' => UuidFactory::uuid('resource.id.enlightenment'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.ada'),
            'modified_by' => UuidFactory::uuid('user.id.ada'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.fosdem'),
            'resource_id' => UuidFactory::uuid('resource.id.fosdem'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.betty'),
            'modified_by' => UuidFactory::uuid('user.id.betty'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.framasoft'),
            'resource_id' => UuidFactory::uuid('resource.id.framasoft'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.carol'),
            'modified_by' => UuidFactory::uuid('user.id.carol'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.fsfe'),
            'resource_id' => UuidFactory::uuid('resource.id.fsfe'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.dame'),
            'modified_by' => UuidFactory::uuid('user.id.dame'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.ftp'),
            'resource_id' => UuidFactory::uuid('resource.id.ftp'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.edith'),
            'modified_by' => UuidFactory::uuid('user.id.edith'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.git'),
            'resource_id' => UuidFactory::uuid('resource.id.git'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.dame'),
            'modified_by' => UuidFactory::uuid('user.id.dame'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.gnupg'),
            'resource_id' => UuidFactory::uuid('resource.id.gnupg'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.carol'),
            'modified_by' => UuidFactory::uuid('user.id.carol'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.grogle'),
            'resource_id' => UuidFactory::uuid('resource.id.grogle'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.ada'),
            'modified_by' => UuidFactory::uuid('user.id.ada'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.grunt'),
            'resource_id' => UuidFactory::uuid('resource.id.grunt'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.betty'),
            'modified_by' => UuidFactory::uuid('user.id.betty'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.inkscape'),
            'resource_id' => UuidFactory::uuid('resource.id.inkscape'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.edith'),
            'modified_by' => UuidFactory::uuid('user.id.edith'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.jquery'),
            'resource_id' => UuidFactory::uuid('resource.id.jquery'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => date('Y-m-d H:i:s'),
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.dame'),
            'modified_by' => UuidFactory::uuid('user.id.dame'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.kde'),
            'resource_id' => UuidFactory::uuid('resource.id.kde'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.betty'),
            'modified_by' => UuidFactory::uuid('user.id.betty'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.linux'),
            'resource_id' => UuidFactory::uuid('resource.id.linux'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.hedy'),
            'modified_by' => UuidFactory::uuid('user.id.hedy'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.mailvelope'),
            'resource_id' => UuidFactory::uuid('resource.id.mailvelope'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.jean'),
            'modified_by' => UuidFactory::uuid('user.id.jean'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.mocha'),
            'resource_id' => UuidFactory::uuid('resource.id.mocha'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.kathleen'),
            'modified_by' => UuidFactory::uuid('user.id.kathleen'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.nodejs'),
            'resource_id' => UuidFactory::uuid('resource.id.nodejs'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.marlyn'),
            'modified_by' => UuidFactory::uuid('user.id.marlyn'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.openpgpjs'),
            'resource_id' => UuidFactory::uuid('resource.id.openpgpjs'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.nancy'),
            'modified_by' => UuidFactory::uuid('user.id.nancy'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.phpunit'),
            'resource_id' => UuidFactory::uuid('resource.id.phpunit'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.ursula'),
            'modified_by' => UuidFactory::uuid('user.id.ursula'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.qgis'),
            'resource_id' => UuidFactory::uuid('resource.id.qgis'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.wang'),
            'modified_by' => UuidFactory::uuid('user.id.wang'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.selenium'),
            'resource_id' => UuidFactory::uuid('resource.id.selenium'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.yvonne'),
            'modified_by' => UuidFactory::uuid('user.id.yvonne'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.stealjs'),
            'resource_id' => UuidFactory::uuid('resource.id.stealjs'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.adele'),
            'modified_by' => UuidFactory::uuid('user.id.adele'),
        ];
        $secretRevisions[] = [
            'id' => UuidFactory::uuid('secretRevision.id.virtualbox'),
            'resource_id' => UuidFactory::uuid('resource.id.virtualbox'),
            'resource_type_id' => UuidFactory::uuid('resource-types.id.password-string'),
            'deleted' => null,
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'modified' => date('Y-m-d H:i:s', strtotime('-1 minutes')),
            'created_by' => UuidFactory::uuid('user.id.adele'),
            'modified_by' => UuidFactory::uuid('user.id.adele'),
        ];

        return $secretRevisions;
    }
}
