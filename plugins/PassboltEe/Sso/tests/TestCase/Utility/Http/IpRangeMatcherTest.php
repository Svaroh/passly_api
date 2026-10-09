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

namespace Passbolt\Sso\Test\TestCase\Utility\Http;

use Passbolt\Sso\Utility\Http\IpRangeMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Passbolt\Sso\Utility\Http\IpRangeMatcher
 */
class IpRangeMatcherTest extends TestCase
{
    /**
     * @return array<array{string, string, bool}>
     */
    public static function inRangeProvider(): array
    {
        return [
            // IPv4 within range.
            ['127.0.0.1', '127.0.0.0/8', true],
            ['10.1.2.3', '10.0.0.0/8', true],
            ['172.16.5.5', '172.16.0.0/12', true],
            ['192.168.1.1', '192.168.0.0/16', true],
            ['169.254.169.254', '169.254.0.0/16', true],
            ['100.64.0.1', '100.64.0.0/10', true],
            // IPv4 outside range.
            ['8.8.8.8', '10.0.0.0/8', false],
            ['172.32.0.1', '172.16.0.0/12', false],
            ['100.128.0.1', '100.64.0.0/10', false],
            // /0 matches everything.
            ['8.8.8.8', '0.0.0.0/0', true],
            // IPv6 within range.
            ['::1', '::1/128', true],
            ['fe80::1', 'fe80::/10', true],
            ['fd00::1', 'fd00::/8', true],
            ['fd00:ec2::254', 'fd00:ec2::254/128', true],
            // IPv6 outside range.
            ['2001:4860:4860::8888', 'fe80::/10', false],
            // Cross family never matches.
            ['127.0.0.1', '::1/128', false],
            ['::1', '127.0.0.0/8', false],
            // IPv4-in-IPv6 embedding ranges (mapped / NAT64 / 6to4).
            ['::ffff:127.0.0.1', '::ffff:0:0/96', true],
            ['64:ff9b::7f00:1', '64:ff9b::/96', true],
            ['2002:a00:1::', '2002::/16', true],
            ['2606:4700:4700::1111', '::ffff:0:0/96', false],
            // Bare IP literal (no slash) must match exactly.
            ['169.254.169.254', '169.254.169.254', true],
            ['169.254.169.253', '169.254.169.254', false],
            // Malformed input.
            ['not-an-ip', '10.0.0.0/8', false],
            ['10.0.0.1', 'not-a-cidr/8', false],
        ];
    }

    /**
     * @dataProvider inRangeProvider
     * @param string $ip IP literal.
     * @param string $cidr CIDR range or bare IP literal.
     * @param bool $expected Expected result.
     * @return void
     */
    public function testIpRangeMatcher_InRange(string $ip, string $cidr, bool $expected): void
    {
        $this->assertSame($expected, IpRangeMatcher::inRange($ip, $cidr));
    }

    public function testIpRangeMatcher_InAnyRange_Success_MatchesOneOfSeveral(): void
    {
        $ranges = ['10.0.0.0/8', '192.168.0.0/16'];
        $this->assertTrue(IpRangeMatcher::inAnyRange('192.168.5.5', $ranges));
        $this->assertFalse(IpRangeMatcher::inAnyRange('8.8.8.8', $ranges));
    }
}
