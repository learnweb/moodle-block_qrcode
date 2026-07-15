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

use BaconQrCode\Exception\OutOfBoundsException;
use DASPRiD\Enum\AbstractEnum;

/**
 * Enum representing the four error correction levels.
 *
 * @copyright 2024 J. Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ErrorCorrectionLevel extends AbstractEnum
{
    /**
     * Low error correction level, approximately 7% correction.
     */
    protected const L = [0x01];
    /**
     * Medium error correction level, approximately 15% correction.
     */
    protected const M = [0x00];
    /**
     * Quartile error correction level, approximately 25% correction.
     */
    protected const Q = [0x03];
    /**
     * High error correction level, approximately 30% correction.
     */
    protected const H = [0x02];

    /**
     * The two bits used to encode this error correction level.
     *
     * @var int
     */
    private readonly int $bits;

    /**
     * Constructor.
     *
     * @param int $bits the two bits used to encode this error correction level
     */
    protected function __construct(int $bits) {
        $this->bits = $bits;
    }

    /**
     * Creates an error correction level from the given bit value.
     *
     * @param int $bits the bit value to decode
     * @return self the matching error correction level
     * @throws OutOfBoundsException if the number of bits is invalid
     */
    public static function for_bits(int $bits): self {
        switch ($bits) {
            case 0:
                return self::M();

            case 1:
                return self::L();

            case 2:
                return self::H();

            case 3:
                return self::Q();
        }

        throw new OutOfBoundsException('Invalid number of bits');
    }

    /**
     * Returns the two bits used to encode this error correction level.
     *
     * @return int the two bits used to encode this error correction level
     */
    public function get_bits(): int {
        return $this->bits;
    }
}
