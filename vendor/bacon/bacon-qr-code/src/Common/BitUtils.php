<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace BaconQrCode\Common;

/**
 * General bit utilities.
 *
 * All utility methods are based on 32-bit integers and also work on 64-bit
 * systems.
 *
 * @copyright 2017 T. Gunkel
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class BitUtils
{
    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct() {
    }

    /**
     * Performs an unsigned right shift.
     *
     * This is the same as the unsigned right shift operator ">>>" in other
     * languages.
     */
    public static function unsignedrightshift(int $a, int $b): int {
        return (
            $a >= 0
            ? $a >> $b
            : (($a & 0x7fffffff) >> $b) | (0x40000000 >> ($b - 1))
        );
    }

    /**
     * Gets the number of trailing zeros.
     */
    public static function numberoftrailingzeros(int $i): int {
        $lastpos = strrpos(str_pad(decbin($i), 32, '0', STR_PAD_LEFT), '1');
        return $lastpos === false ? 32 : 31 - $lastpos;
    }
}
