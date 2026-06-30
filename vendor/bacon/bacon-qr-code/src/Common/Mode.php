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

use DASPRiD\Enum\AbstractEnum;

/**
 * Enum representing various modes in which data can be encoded to bits.
 *
 * @copyright  2024 Justus Dieckmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Mode extends AbstractEnum
{
    /**
     * Terminator mode.
     */
    protected const TERMINATOR = [[0, 0, 0], 0x00];

    /**
     * Numeric encoding mode.
     */
    protected const NUMERIC = [[10, 12, 14], 0x01];

    /**
     * Alphanumeric encoding mode.
     */
    protected const ALPHANUMERIC = [[9, 11, 13], 0x02];

    /**
     * Structured append encoding mode.
     */
    protected const STRUCTURED_APPEND = [[0, 0, 0], 0x03];

    /**
     * Byte encoding mode.
     */
    protected const BYTE = [[8, 16, 16], 0x04];

    /**
     * Extended Channel Interpretation encoding mode.
     */
    protected const ECI = [[0, 0, 0], 0x07];

    /**
     * Kanji encoding mode.
     */
    protected const KANJI = [[8, 10, 12], 0x08];

    /**
     * FNC1 first position encoding mode.
     */
    protected const FNC1_FIRST_POSITION = [[0, 0, 0], 0x05];

    /**
     * FNC1 second position encoding mode.
     */
    protected const FNC1_SECOND_POSITION = [[0, 0, 0], 0x09];

    /**
     * Hanzi encoding mode.
     */
    protected const HANZI = [[8, 10, 12], 0x0d];

    /**
     * Creates a new encoding mode.
     *
     * @param int[] $charactercountbitsforversions
     */
    protected function __construct(
        /**
         * @var array
         */
        private readonly array $charactercountbitsforversions,
        /**
         * @var int
         */
        private readonly int $bits
    ) {
    }

    /**
     * Returns the number of bits used in a specific QR code version.
     */
    public function getcharactercountbits(Version $version): int {
        $number = $version->getversionnumber();

        if ($number <= 9) {
            $offset = 0;
        } else if ($number <= 26) {
            $offset = 1;
        } else {
            $offset = 2;
        }

        return $this->charactercountbitsforversions[$offset];
    }

    /**
     * Returns the four bits used to encode this mode.
     */
    public function getbits(): int {
        return $this->bits;
    }
}
