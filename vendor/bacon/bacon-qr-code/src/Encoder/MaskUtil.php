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

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\BitUtils;
use BaconQrCode\Exception\InvalidArgumentException;

/**
 * Mask utility.
 *
 * @copyright  2017 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class MaskUtil
{
    /**
     *  First penalty weight from section 6.8.2.1.
     */
    public const N1 = 3;
    /**
     *  Second penalty weight from section 6.8.2.1.
     */
    public const N2 = 3;
    /**
     *  Third penalty weight from section 6.8.2.1.
     */
    public const N3 = 40;
    /**
     *  Fourth penalty weight from section 6.8.2.1.
     */
    public const N4 = 10;

    /**
     * Constructor.
     */
    private function __construct() {
    }

    /**
     * Applies mask penalty rule 1 and returns the penalty.
     *
     * Finds repetitive cells with the same color and gives penalty to them.
     * Example: 00000 or 11111.
     */
    public static function applymaskpenaltyrule1(ByteMatrix $matrix): int {
        return (
            self::applymaskpenaltyrule1internal($matrix, true)
            + self::applymaskpenaltyrule1internal($matrix, false)
        );
    }

    /**
     * Applies mask penalty rule 2 and returns the penalty.
     *
     * Finds 2x2 blocks with the same color and gives penalty to them. This is
     * actually equivalent to the spec's rule, which is to find MxN blocks and
     * give a penalty proportional to (M-1)x(N-1), because this is the number of
     * 2x2 blocks inside such a block.
     */
    public static function applymaskpenaltyrule2(ByteMatrix $matrix): int {
        $penalty = 0;
        $array = $matrix->getArray();
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();

        for ($y = 0; $y < $height - 1; ++$y) {
            for ($x = 0; $x < $width - 1; ++$x) {
                $value = $array[$y][$x];

                if (
                    $value === $array[$y][$x + 1]
                    && $value === $array[$y + 1][$x]
                    && $value === $array[$y + 1][$x + 1]
                ) {
                    ++$penalty;
                }
            }
        }

        return self::N2 * $penalty;
    }

    /**
     * Applies mask penalty rule 3 and returns the penalty.
     *
     * Finds consecutive cells of 00001011101 or 10111010000, and gives penalty
     * to them. If we find patterns like 000010111010000, we give penalties
     * twice (i.e. 40 * 2).
     */
    public static function applymaskpenaltyrule3(ByteMatrix $matrix): int {
        $penalty = 0;
        $array = $matrix->getArray();
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();

        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                if (
                    $x + 6 < $width
                    && 1 === $array[$y][$x]
                    && 0 === $array[$y][$x + 1]
                    && 1 === $array[$y][$x + 2]
                    && 1 === $array[$y][$x + 3]
                    && 1 === $array[$y][$x + 4]
                    && 0 === $array[$y][$x + 5]
                    && 1 === $array[$y][$x + 6]
                    && (
                        (
                            $x + 10 < $width
                            && 0 === $array[$y][$x + 7]
                            && 0 === $array[$y][$x + 8]
                            && 0 === $array[$y][$x + 9]
                            && 0 === $array[$y][$x + 10]
                        )
                        || (
                            $x - 4 >= 0
                            && 0 === $array[$y][$x - 1]
                            && 0 === $array[$y][$x - 2]
                            && 0 === $array[$y][$x - 3]
                            && 0 === $array[$y][$x - 4]
                        )
                    )
                ) {
                    $penalty += self::N3;
                }

                if (
                    $y + 6 < $height
                    && 1 === $array[$y][$x]
                    && 0 === $array[$y + 1][$x]
                    && 1 === $array[$y + 2][$x]
                    && 1 === $array[$y + 3][$x]
                    && 1 === $array[$y + 4][$x]
                    && 0 === $array[$y + 5][$x]
                    && 1 === $array[$y + 6][$x]
                    && (
                        (
                            $y + 10 < $height
                            && 0 === $array[$y + 7][$x]
                            && 0 === $array[$y + 8][$x]
                            && 0 === $array[$y + 9][$x]
                            && 0 === $array[$y + 10][$x]
                        )
                        || (
                            $y - 4 >= 0
                            && 0 === $array[$y - 1][$x]
                            && 0 === $array[$y - 2][$x]
                            && 0 === $array[$y - 3][$x]
                            && 0 === $array[$y - 4][$x]
                        )
                    )
                ) {
                    $penalty += self::N3;
                }
            }
        }

        return $penalty;
    }

    /**
     * Applies mask penalty rule 4 and returns the penalty.
     *
     * Calculates the ratio of dark cells and gives penalty if the ratio is far from 50%. It gives 10 penalty for 5% distance.
     */
    public static function applymaskpenaltyrule4(ByteMatrix $matrix): int {
        $numdarkcells = 0;

        $array = $matrix->getArray();
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();

        for ($y = 0; $y < $height; ++$y) {
            $arrayy = $array[$y];

            for ($x = 0; $x < $width; ++$x) {
                if (1 === $arrayy[$x]) {
                    ++$numdarkcells;
                }
            }
        }

        $numtotalcells = $height * $width;
        $darkratio = $numdarkcells / $numtotalcells;
        $fixedpercentvariances = (int) (abs($darkratio - 0.5) * 20);

        return $fixedpercentvariances * self::N4;
    }

    /**
     * Returns the mask bit for "getMaskPattern" at "x" and "y".
     *
     * See 8.8 of JISX0510:2004 for mask pattern conditions.
     *
     * @throws InvalidArgumentException if an invalid mask pattern was supplied
     */
    public static function getdatamaskbit(int $maskpattern, int $x, int $y): bool {
        switch ($maskpattern) {
            case 0:
                $intermediate = ($y + $x) & 0x1;
                break;

            case 1:
                $intermediate = $y & 0x1;
                break;

            case 2:
                $intermediate = $x % 3;
                break;

            case 3:
                $intermediate = ($y + $x) % 3;
                break;

            case 4:
                $intermediate = (BitUtils::unsignedrightshift($y, 1) + (int) ($x / 3)) & 0x1;
                break;

            case 5:
                $temp = $y * $x;
                $intermediate = ($temp & 0x1) + ($temp % 3);
                break;

            case 6:
                $temp = $y * $x;
                $intermediate = (($temp & 0x1) + ($temp % 3)) & 0x1;
                break;

            case 7:
                $temp = $y * $x;
                $intermediate = (($temp % 3) + (($y + $x) & 0x1)) & 0x1;
                break;

            default:
                throw new InvalidArgumentException('Invalid mask pattern: ' . $maskpattern);
        }

        return 0 == $intermediate;
    }

    /**
     * Helper function for applyMaskPenaltyRule1.
     *
     * We need this for doing this calculation in both vertical and horizontal
     * orders respectively.
     */
    private static function applymaskpenaltyrule1internal(ByteMatrix $matrix, bool $ishorizontal): int {
        $penalty = 0;
        $ilimit = $ishorizontal ? $matrix->getHeight() : $matrix->getWidth();
        $jlimit = $ishorizontal ? $matrix->getWidth() : $matrix->getHeight();
        $array = $matrix->getArray();

        for ($i = 0; $i < $ilimit; ++$i) {
            $numsamebitcells = 0;
            $prevbit = -1;

            for ($j = 0; $j < $jlimit; $j++) {
                $bit = $ishorizontal ? $array[$i][$j] : $array[$j][$i];

                if ($bit === $prevbit) {
                    ++$numsamebitcells;
                } else {
                    if ($numsamebitcells >= 5) {
                        $penalty += self::N1 + ($numsamebitcells - 5);
                    }

                    $numsamebitcells = 1;
                    $prevbit = $bit;
                }
            }

            if ($numsamebitcells >= 5) {
                $penalty += self::N1 + ($numsamebitcells - 5);
            }
        }

        return $penalty;
    }
}
