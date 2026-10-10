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

namespace Passbolt\Sso\Utility\Http;

/**
 * Matches an IP address against CIDR ranges (IPv4 and IPv6).
 */
class IpRangeMatcher
{
    /**
     * Returns true if the given IP falls within any of the provided CIDR ranges.
     *
     * @param string $ip An IP literal (v4 or v6).
     * @param array<string> $cidrs List of CIDR ranges (or bare IP literals).
     * @return bool
     */
    public static function inAnyRange(string $ip, array $cidrs): bool
    {
        foreach ($cidrs as $cidr) {
            if (self::inRange($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the given IP falls within the provided CIDR range.
     *
     * @param string $ip A normalised IP literal (v4 or v6).
     * @param string $cidr A CIDR range (e.g. "10.0.0.0/8", "fe80::/10") or a bare IP literal.
     * @return bool
     */
    public static function inRange(string $ip, string $cidr): bool
    {
        $ipBin = self::toBinary($ip);
        if ($ipBin === null) {
            return false;
        }

        // A bare IP literal must match exactly.
        if (!str_contains($cidr, '/')) {
            $cidrBin = self::toBinary($cidr);

            return $cidrBin !== null && $ipBin === $cidrBin;
        }

        [$subnet, $prefix] = explode('/', $cidr, 2);
        $subnetBin = self::toBinary($subnet);
        if ($subnetBin === null) {
            return false;
        }

        // Different address families (IPv4 vs IPv6) can never match.
        if (strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $prefixBits = (int)$prefix;
        $wholeBytes = intdiv($prefixBits, 8);
        $remainderBits = $prefixBits % 8;

        if ($wholeBytes > 0 && strncmp($ipBin, $subnetBin, $wholeBytes) !== 0) {
            return false;
        }
        if ($remainderBits === 0) {
            return true;
        }

        $mask = chr(0xFF << 8 - $remainderBits & 0xFF);

        return (($ipBin[$wholeBytes] ^ $subnetBin[$wholeBytes]) & $mask) === "\0";
    }

    /**
     * Convert an IP literal to its packed in_addr representation, or null when the value is not a
     * valid IP address.
     *
     * @param string $ip IP literal (v4 or v6).
     * @return string|null
     */
    private static function toBinary(string $ip): ?string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }
        $binary = inet_pton($ip);

        return $binary === false ? null : $binary;
    }
}
