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
 * @since         5.12.0
 */

namespace Passbolt\SsoRecover\Test\TestCase\Controller\Adfs;

use Passbolt\Sso\Model\Entity\SsoState;
use Passbolt\Sso\Test\Factory\SsoAuthenticationTokenFactory;
use Passbolt\SsoRecover\Test\Lib\SsoRecoverIntegrationTestCase;

/**
 * @covers \Passbolt\SsoRecover\Controller\Adfs\AdfsRecoverSuccessController
 */
class AdfsRecoverSuccessControllerTest extends SsoRecoverIntegrationTestCase
{
    public function testAdfsRecoverSuccessController_ErrorJson(): void
    {
        $this->getJson('/sso/recover/adfs/success.json');
        $this->assertError(400, 'not supported');
    }

    public function testAdfsRecoverSuccessController_ErrorLoggedIn(): void
    {
        $this->logInAsUser();
        $this->get('/sso/recover/adfs/success');

        $this->assertResponseCode(403);
        $this->assertResponseContains('The user should not be logged in.');
    }

    public function testAdfsRecoverSuccessController_ErrorNoToken(): void
    {
        $this->get('/sso/recover/adfs/success');

        $this->assertResponseCode(400);
        $this->assertResponseContains('The token is required in URL parameters.');
    }

    public function testAdfsRecoverSuccessController_ErrorInvalidToken(): void
    {
        $this->get('/sso/recover/adfs/success?token=nope');

        $this->assertResponseCode(400);
        $this->assertResponseContains('The token is required in URL parameters.');
    }

    public function testAdfsRecoverSuccessController_ErrorTokenDeleted(): void
    {
        /** @var \Passbolt\Sso\Model\Entity\SsoAuthenticationToken $authToken */
        $authToken = SsoAuthenticationTokenFactory::make()
            ->type(SsoState::TYPE_SSO_RECOVER)
            ->inactive()
            ->persist();

        $this->get('/sso/recover/adfs/success?token=' . $authToken->token);

        $this->assertResponseCode(400);
        $this->assertResponseContains('The authentication token does not exist or has been deleted.');
    }

    public function testAdfsRecoverSuccessController_ErrorTokenExpired(): void
    {
        /** @var \Passbolt\Sso\Model\Entity\SsoAuthenticationToken $authToken */
        $authToken = SsoAuthenticationTokenFactory::make()->type(SsoState::TYPE_SSO_RECOVER)->expired()->persist();

        $this->get('/sso/recover/adfs/success?token=' . $authToken->token);

        $this->assertResponseCode(400);
        $this->assertResponseContains('The authentication token has been expired.');
    }

    public function testAdfsRecoverSuccessController_Success(): void
    {
        /** @var \Passbolt\Sso\Model\Entity\SsoAuthenticationToken $authToken */
        $authToken = SsoAuthenticationTokenFactory::make()->type(SsoState::TYPE_SSO_RECOVER)->active()->persist();

        $this->get('/sso/recover/adfs/success?token=' . $authToken->token);

        $this->assertResponseCode(200);
        $this->assertResponseContains('success');
        $this->assertResponseContains('/js/app/api-feedback.js');
        $this->assertResponseContains('id="api-success"');
    }
}
