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

use BaconQrCode\Common\BitArray;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Version;
use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Exception\WriterException;

/**
 * Matrix utility.
 *
 * @copyright  2024 Justus Dieckmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class MatrixUtil
{
    /**
     * Position detection pattern.
     */
    private const POSITION_DETECTION_PATTERN = [
        [1, 1, 1, 1, 1, 1, 1],
        [1, 0, 0, 0, 0, 0, 1],
        [1, 0, 1, 1, 1, 0, 1],
        [1, 0, 1, 1, 1, 0, 1],
        [1, 0, 1, 1, 1, 0, 1],
        [1, 0, 0, 0, 0, 0, 1],
        [1, 1, 1, 1, 1, 1, 1],
    ];

    /**
     * Position adjustment pattern.
     */
    private const POSITION_ADJUSTMENT_PATTERN = [
        [1, 1, 1, 1, 1],
        [1, 0, 0, 0, 1],
        [1, 0, 1, 0, 1],
        [1, 0, 0, 0, 1],
        [1, 1, 1, 1, 1],
    ];

    /**
     * Coordinates for position adjustment patterns for each version.
     */
    private const POSITION_ADJUSTMENT_PATTERN_COORDINATE_TABLE = [
        [null, null, null, null, null, null, null], // Version 1.
        [   6, 18, null, null, null, null, null], // Version 2.
        [   6, 22, null, null, null, null, null], // Version 3.
        [   6, 26, null, null, null, null, null], // Version 4.
        [   6, 30, null, null, null, null, null], // Version 5.
        [   6, 34, null, null, null, null, null], // Version 6.
        [   6, 22, 38, null, null, null, null], // Version 7.
        [   6, 24, 42, null, null, null, null], // Version 8.
        [   6, 26, 46, null, null, null, null], // Version 9.
        [   6, 28, 50, null, null, null, null], // Version 10.
        [   6, 30, 54, null, null, null, null], // Version 11.
        [   6, 32, 58, null, null, null, null], // Version 12.
        [   6, 34, 62, null, null, null, null], // Version 13.
        [   6, 26, 46, 66, null, null, null], // Version 14.
        [   6, 26, 48, 70, null, null, null], // Version 15.
        [   6, 26, 50, 74, null, null, null], // Version 16.
        [   6, 30, 54, 78, null, null, null], // Version 17.
        [   6, 30, 56, 82, null, null, null], // Version 18.
        [   6, 30, 58, 86, null, null, null], // Version 19.
        [   6, 34, 62, 90, null, null, null], // Version 20.
        [   6, 28, 50, 72, 94, null, null], // Version 21.
        [   6, 26, 50, 74, 98, null, null], // Version 22.
        [   6, 30, 54, 78, 102, null, null], // Version 23.
        [   6, 28, 54, 80, 106, null, null], // Version 24.
        [   6, 32, 58, 84, 110, null, null], // Version 25.
        [   6, 30, 58, 86, 114, null, null], // Version 26.
        [   6, 34, 62, 90, 118, null, null], // Version 27.
        [   6, 26, 50, 74, 98, 122, null], // Version 28.
        [   6, 30, 54, 78, 102, 126, null], // Version 29.
        [   6, 26, 52, 78, 104, 130, null], // Version 30.
        [   6, 30, 56, 82, 108, 134, null], // Version 31.
        [   6, 34, 60, 86, 112, 138, null], // Version 32.
        [   6, 30, 58, 86, 114, 142, null], // Version 33.
        [   6, 34, 62, 90, 118, 146, null], // Version 34.
        [   6, 30, 54, 78, 102, 126, 150], // Version 35.
        [   6, 24, 50, 76, 102, 128, 154], // Version 36.
        [   6, 28, 54, 80, 106, 132, 158], // Version 37.
        [   6, 32, 58, 84, 110, 136, 162], // Version 38.
        [   6, 26, 54, 82, 110, 138, 166], // Version 39.
        [   6, 30, 58, 86, 114, 142, 170], // Version 40.
    ];

    /**
     * Type information coordinates.
     */
    private const TYPE_INFO_COORDINATES = [
        [8, 0],
        [8, 1],
        [8, 2],
        [8, 3],
        [8, 4],
        [8, 5],
        [8, 7],
        [8, 8],
        [7, 8],
        [5, 8],
        [4, 8],
        [3, 8],
        [2, 8],
        [1, 8],
        [0, 8],
    ];

    /**
     * Version information polynomial.
     */
    private const VERSION_INFO_POLY = 0x1f25;

    /**
     * Type information polynomial.
     */
    private const TYPE_INFO_POLY = 0x537;

    /**
     * Type information mask pattern.
     */
    private const TYPE_INFO_MASK_PATTERN = 0x5412;

    /**
     * Clears a given matrix.
     */
    public static function clearmatrix(ByteMatrix $matrix): void {
        $matrix->clear(-1);
    }

    /**
     * Builds a complete matrix.
     */
    public static function buildmatrix(
        BitArray $databits,
        ErrorCorrectionLevel $level,
        Version $version,
        int $maskpattern,
        ByteMatrix $matrix
    ): void {
        self::clearmatrix($matrix);
        self::embedbasicpatterns($version, $matrix);
        self::embedtypeinfo($level, $maskpattern, $matrix);
        self::maybeembedversioninfo($version, $matrix);
        self::embeddatabits($databits, $maskpattern, $matrix);
    }

    /**
     * Removes the position detection patterns from a matrix.
     *
     * This can be useful if you need to render those patterns separately.
     */
    public static function removepositiondetectionpatterns(ByteMatrix $matrix): void {
        $pdpwidth = count(self::POSITION_DETECTION_PATTERN[0]);

        self::removepositiondetectionpattern(0, 0, $matrix);
        self::removepositiondetectionpattern($matrix->getWidth() - $pdpwidth, 0, $matrix);
        self::removepositiondetectionpattern(0, $matrix->getWidth() - $pdpwidth, $matrix);
    }

    /**
     * Embeds type information into a matrix.
     */
    private static function embedtypeinfo(ErrorCorrectionLevel $level, int $maskpattern, ByteMatrix $matrix): void {
        $typeinfobits = new BitArray();
        self::maketypeinfobits($level, $maskpattern, $typeinfobits);

        $typeinfobitssize = $typeinfobits->get_size();

        for ($i = 0; $i < $typeinfobitssize; ++$i) {
            $bit = $typeinfobits->get($typeinfobitssize - 1 - $i);

            $x1 = self::TYPE_INFO_COORDINATES[$i][0];
            $y1 = self::TYPE_INFO_COORDINATES[$i][1];

            $matrix->set($x1, $y1, (int) $bit);

            if ($i < 8) {
                $x2 = $matrix->getWidth() - $i - 1;
                $y2 = 8;
            } else {
                $x2 = 8;
                $y2 = $matrix->getHeight() - 7 + ($i - 8);
            }

            $matrix->set($x2, $y2, (int) $bit);
        }
    }

    /**
     * Generates type information bits and appends them to a bit array.
     *
     * @throws RuntimeException if bit array resulted in invalid size
     */
    private static function maketypeinfobits(ErrorCorrectionLevel $level, int $maskpattern, BitArray $bits): void {
        $typeinfo = ($level->getbits() << 3) | $maskpattern;
        $bits->append_bits($typeinfo, 5);

        $bchcode = self::calculatebchcode($typeinfo, self::TYPE_INFO_POLY);
        $bits->append_bits($bchcode, 10);

        $maskbits = new BitArray();
        $maskbits->append_bits(self::TYPE_INFO_MASK_PATTERN, 15);
        $bits->xor_bits($maskbits);

        if (15 !== $bits->get_size()) {
            throw new RuntimeException('Bit array resulted in invalid size: ' . $bits->get_size());
        }
    }

    /**
     * Embeds version information if required.
     */
    private static function maybeembedversioninfo(Version $version, ByteMatrix $matrix): void {
        if ($version->get_version_number() < 7) {
            return;
        }

        $versioninfobits = new BitArray();
        self::makeversioninfobits($version, $versioninfobits);

        $bitindex = 6 * 3 - 1;

        for ($i = 0; $i < 6; ++$i) {
            for ($j = 0; $j < 3; ++$j) {
                $bit = $versioninfobits->get($bitindex);
                --$bitindex;

                $matrix->set($i, $matrix->getHeight() - 11 + $j, (int) $bit);
                $matrix->set($matrix->getHeight() - 11 + $j, $i, (int) $bit);
            }
        }
    }

    /**
     * Generates version information bits and appends them to a bit array.
     *
     * @throws RuntimeException if bit array resulted in invalid size
     */
    private static function makeversioninfobits(Version $version, BitArray $bits): void {
        $bits->append_bits($version->get_version_number(), 6);

        $bchcode = self::calculatebchcode($version->get_version_number(), self::VERSION_INFO_POLY);
        $bits->append_bits($bchcode, 12);

        if (18 !== $bits->get_size()) {
            throw new RuntimeException('Bit array resulted in invalid size: ' . $bits->get_size());
        }
    }

    /**
     * Calculates the BCH code for a value and a polynomial.
     */
    private static function calculatebchcode(int $value, int $poly): int {
        $msbsetinpoly = self::findmsbset($poly);
        $value <<= $msbsetinpoly - 1;

        while (self::findmsbset($value) >= $msbsetinpoly) {
            $value ^= $poly << (self::findmsbset($value) - $msbsetinpoly);
        }

        return $value;
    }

    /**
     * Finds and MSB set.
     */
    private static function findmsbset(int $value): int {
        $numdigits = 0;

        while (0 !== $value) {
            $value >>= 1;
            ++$numdigits;
        }

        return $numdigits;
    }

    /**
     * Embeds basic patterns into a matrix.
     */
    private static function embedbasicpatterns(Version $version, ByteMatrix $matrix): void {
        self::embedpositiondetectionpatternsandseparators($matrix);
        self::embeddarkdotatleftbottomcorner($matrix);
        self::maybeembedpositionadjustmentpatterns($version, $matrix);
        self::embedtimingpatterns($matrix);
    }

    /**
     * Embeds position detection patterns and separators into a byte matrix.
     */
    private static function embedpositiondetectionpatternsandseparators(ByteMatrix $matrix): void {
        $pdpwidth = count(self::POSITION_DETECTION_PATTERN[0]);

        self::embedpositiondetectionpattern(0, 0, $matrix);
        self::embedpositiondetectionpattern($matrix->getWidth() - $pdpwidth, 0, $matrix);
        self::embedpositiondetectionpattern(0, $matrix->getWidth() - $pdpwidth, $matrix);

        $hspwidth = 8;

        self::embedhorizontalseparationpattern(0, $hspwidth - 1, $matrix);
        self::embedhorizontalseparationpattern($matrix->getWidth() - $hspwidth, $hspwidth - 1, $matrix);
        self::embedhorizontalseparationpattern(0, $matrix->getWidth() - $hspwidth, $matrix);

        $vspsize = 7;

        self::embedverticalseparationpattern($vspsize, 0, $matrix);
        self::embedverticalseparationpattern($matrix->getHeight() - $vspsize - 1, 0, $matrix);
        self::embedverticalseparationpattern($vspsize, $matrix->getHeight() - $vspsize, $matrix);
    }

    /**
     * Embeds a single position detection pattern into a byte matrix.
     */
    private static function embedpositiondetectionpattern(int $xstart, int $ystart, ByteMatrix $matrix): void {
        for ($y = 0; $y < 7; ++$y) {
            for ($x = 0; $x < 7; ++$x) {
                $matrix->set($xstart + $x, $ystart + $y, self::POSITION_DETECTION_PATTERN[$y][$x]);
            }
        }
    }

    /**
     * Removes a position detection pattern from the matrix.
     *
     * This clears the 7x7 finder pattern area starting at the given coordinates by setting all affected matrix cells to 0.
     *
     * @param int $xstart
     * @param int $ystart
     * @param ByteMatrix $matrix
     * @return void
     */
    private static function removepositiondetectionpattern(int $xstart, int $ystart, ByteMatrix $matrix): void {
        for ($y = 0; $y < 7; ++$y) {
            for ($x = 0; $x < 7; ++$x) {
                $matrix->set($xstart + $x, $ystart + $y, 0);
            }
        }
    }

    /**
     * Embeds a single horizontal separation pattern.
     *
     * @throws RuntimeException if a byte was already set
     */
    private static function embedhorizontalseparationpattern(int $xstart, int $ystart, ByteMatrix $matrix): void {
        for ($x = 0; $x < 8; $x++) {
            if (-1 !== $matrix->get($xstart + $x, $ystart)) {
                throw new RuntimeException('Byte already set');
            }

            $matrix->set($xstart + $x, $ystart, 0);
        }
    }

    /**
     * Embeds a single vertical separation pattern.
     *
     * @throws RuntimeException if a byte was already set
     */
    private static function embedverticalseparationpattern(int $xstart, int $ystart, ByteMatrix $matrix): void {
        for ($y = 0; $y < 7; $y++) {
            if (-1 !== $matrix->get($xstart, $ystart + $y)) {
                throw new RuntimeException('Byte already set');
            }

            $matrix->set($xstart, $ystart + $y, 0);
        }
    }

    /**
     * Embeds a dot at the left bottom corner.
     *
     * @throws RuntimeException if a byte was already set to 0
     */
    private static function embeddarkdotatleftbottomcorner(ByteMatrix $matrix): void {
        if (0 === $matrix->get(8, $matrix->getHeight() - 8)) {
            throw new RuntimeException('Byte already set to 0');
        }

        $matrix->set(8, $matrix->getHeight() - 8, 1);
    }

    /**
     * Embeds position adjustment patterns if required.
     */
    private static function maybeembedpositionadjustmentpatterns(Version $version, ByteMatrix $matrix): void {
        if ($version->get_version_number() < 2) {
            return;
        }

        $index = $version->get_version_number() - 1;

        $coordinates = self::POSITION_ADJUSTMENT_PATTERN_COORDINATE_TABLE[$index];
        $numcoordinates = count($coordinates);

        for ($i = 0; $i < $numcoordinates; ++$i) {
            for ($j = 0; $j < $numcoordinates; ++$j) {
                $y = $coordinates[$i];
                $x = $coordinates[$j];

                if (null === $x || null === $y) {
                    continue;
                }

                if (-1 === $matrix->get($x, $y)) {
                    self::embedpositionadjustmentpattern($x - 2, $y - 2, $matrix);
                }
            }
        }
    }

    /**
     * Embeds a single position adjustment pattern.
     */
    private static function embedpositionadjustmentpattern(int $xstart, int $ystart, ByteMatrix $matrix): void {
        for ($y = 0; $y < 5; $y++) {
            for ($x = 0; $x < 5; $x++) {
                $matrix->set($xstart + $x, $ystart + $y, self::POSITION_ADJUSTMENT_PATTERN[$y][$x]);
            }
        }
    }

    /**
     * Embeds timing patterns into a matrix.
     */
    private static function embedtimingpatterns(ByteMatrix $matrix): void {
        $matrixwidth = $matrix->getWidth();

        for ($i = 8; $i < $matrixwidth - 8; ++$i) {
            $bit = ($i + 1) % 2;

            if (-1 === $matrix->get($i, 6)) {
                $matrix->set($i, 6, $bit);
            }

            if (-1 === $matrix->get(6, $i)) {
                $matrix->set(6, $i, $bit);
            }
        }
    }

    /**
     * Embeds "dataBits" using "getMaskPattern".
     *
     * For debugging purposes, it skips masking process if "getMaskPattern" is -1. See 8.7 of JISX0510:2004 (p.38) for
     * how to embed data bits.
     *
     * @throws WriterException if not all bits could be consumed
     */
    private static function embeddatabits(BitArray $databits, int $maskpattern, ByteMatrix $matrix): void {
        $bitindex = 0;
        $direction = -1;

        // Start from the right bottom cell.
        $x = $matrix->getWidth() - 1;
        $y = $matrix->getHeight() - 1;

        while ($x > 0) {
            // Skip vertical timing pattern.
            if (6 === $x) {
                --$x;
            }

            while ($y >= 0 && $y < $matrix->getHeight()) {
                for ($i = 0; $i < 2; $i++) {
                    $xx = $x - $i;

                    // Skip the cell if it's not empty.
                    if (-1 !== $matrix->get($xx, $y)) {
                        continue;
                    }

                    if ($bitindex < $databits->get_size()) {
                        $bit = $databits->get($bitindex);
                        ++$bitindex;
                    } else {
                        /* Padding bit. If there is no bit left, we'll fill the
                        left cells with 0, as described in 8.4.9 of
                        JISX0510:2004 (p. 24). */
                        $bit = false;
                    }

                    // Skip masking if maskPattern is -1.
                    if (-1 !== $maskpattern && MaskUtil::get_data_mask_bit($maskpattern, $xx, $y)) {
                        $bit = ! $bit;
                    }

                    $matrix->set($xx, $y, (int) $bit);
                }

                $y += $direction;
            }

            $direction  = -$direction;
            $y += $direction;
            $x -= 2;
        }

        // All bits should be consumed.
        if ($databits->get_size() !== $bitindex) {
            throw new WriterException('Not all bits consumed (' . $bitindex . ' out of ' . $databits->get_size() . ')');
        }
    }
}
