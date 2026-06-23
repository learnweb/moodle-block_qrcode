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
 * @method static self L() ~7% correction
 * @method static self M() ~15% correction
 * @method static self Q() ~25% correction
 * @method static self H() ~30% correction
 */
final class ErrorCorrectionLevel extends AbstractEnum
{
    protected const L = [0x01];
    protected const M = [0x00];
    protected const Q = [0x03];
    protected const H = [0x02];

    protected function __construct(private readonly int $bits) {
    }

    /**
     * @throws OutOfBoundsException if number of bits is invalid
     */
    public static function forBits(int $bits): self {
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
     */
    public function getBits(): int {
        return $this->bits;
    }
}
