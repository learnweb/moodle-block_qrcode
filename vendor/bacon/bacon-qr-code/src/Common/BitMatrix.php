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

use BaconQrCode\Exception\InvalidArgumentException;
use SplFixedArray;

/**
 * Bit matrix.
 *
 * Represents a 2D matrix of bits. In function arguments below, and throughout
 * the common module, x is the column position, and y is the row position. The
 * ordering is always x, y. The origin is at the top-left.
 *
 * @copyright 2017 T. Gunkel
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BitMatrix
{
    /**
     * Width of the bit matrix.
     *
     * @var int
     */
    private int $width;

    /**
     * Height of the bit matrix.
     *
     * @var int|null
     */
    private ?int $height;

    /**
     * Size in bits of each individual row.
     *
     * @var int
     */
    private int $rowsize;

    /**
     * Bits representation.
     *
     * @var SplFixedArray<int>
     */
    private SplFixedArray $bits;

    /**
     * Creates a square bit matrix with the given width.
     * @throws InvalidArgumentException if a dimension is smaller than zero
     */
    public function __construct(int $width, ?int $height = null) {
        if (null === $height) {
            $height = $width;
        }

        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Both dimensions must be greater than zero');
        }

        $this->width = $width;
        $this->height = $height;
        $this->rowsize = ($width + 31) >> 5;
        $this->bits = SplFixedArray::fromArray(array_fill(0, $this->rowsize * $height, 0));
    }

    /**
     * Gets the requested bit, where true means black.
     */
    public function get(int $x, int $y): bool {
        $offset = $y * $this->rowsize + ($x >> 5);
        return 0 !== (BitUtils::unsignedrightshift($this->bits[$offset], ($x & 0x1f)) & 1);
    }

    /**
     * Sets the given bit to true.
     */
    public function set(int $x, int $y): void {
        $offset = $y * $this->rowsize + ($x >> 5);
        $this->bits[$offset] = $this->bits[$offset] | (1 << ($x & 0x1f));
    }

    /**
     * Flips the given bit.
     */
    public function flip(int $x, int $y): void {
        $offset = $y * $this->rowsize + ($x >> 5);
        $this->bits[$offset] = $this->bits[$offset] ^ (1 << ($x & 0x1f));
    }

    /**
     * Clears all bits (set to false).
     */
    public function clear(): void {
        $max = count($this->bits);

        for ($i = 0; $i < $max; ++$i) {
            $this->bits[$i] = 0;
        }
    }

    /**
     * Sets a square region of the bit matrix to true.
     *
     * @throws InvalidArgumentException if left or top are negative
     * @throws InvalidArgumentException if width or height are smaller than 1
     * @throws InvalidArgumentException if region does not fit into the matix
     */
    public function setregion(int $left, int $top, int $width, int $height): void {
        if ($top < 0 || $left < 0) {
            throw new InvalidArgumentException('Left and top must be non-negative');
        }

        if ($height < 1 || $width < 1) {
            throw new InvalidArgumentException('Width and height must be at least 1');
        }

        $right = $left + $width;
        $bottom = $top + $height;

        if ($bottom > $this->height || $right > $this->width) {
            throw new InvalidArgumentException('The region must fit inside the matrix');
        }

        for ($y = $top; $y < $bottom; ++$y) {
            $offset = $y * $this->rowsize;

            for ($x = $left; $x < $right; ++$x) {
                $index = $offset + ($x >> 5);
                $this->bits[$index] = $this->bits[$index] | (1 << ($x & 0x1f));
            }
        }
    }

    /**
     * A fast method to retrieve one row of data from the matrix as a BitArray.
     */
    public function getrow(int $y, ?BitArray $row = null): BitArray {
        if (null === $row || $row->getsize() < $this->width) {
            $row = new BitArray($this->width);
        }

        $offset = $y * $this->rowsize;

        for ($x = 0; $x < $this->rowsize; ++$x) {
            $row->setbulk($x << 5, $this->bits[$offset + $x]);
        }

        return $row;
    }

    /**
     * Sets a row of data from a BitArray.
     */
    public function setrow(int $y, BitArray $row): void {
        $bits = $row->getbitarray();

        for ($i = 0; $i < $this->rowsize; ++$i) {
            $this->bits[$y * $this->rowsize + $i] = $bits[$i];
        }
    }

    /**
     * This is useful in detecting the enclosing rectangle of a 'pure' barcode.
     *
     * @return int[]|null
     */
    public function getenclosingrectangle(): ?array {
        $left = $this->width;
        $top = $this->height;
        $right = -1;
        $bottom = -1;

        for ($y = 0; $y < $this->height; ++$y) {
            for ($x32 = 0; $x32 < $this->rowsize; ++$x32) {
                $bits = $this->bits[$y * $this->rowsize + $x32];

                if (0 !== $bits) {
                    if ($y < $top) {
                        $top = $y;
                    }

                    if ($y > $bottom) {
                        $bottom = $y;
                    }

                    if ($x32 * 32 < $left) {
                        $bit = 0;

                        while (($bits << (31 - $bit)) === 0) {
                            $bit++;
                        }

                        if (($x32 * 32 + $bit) < $left) {
                            $left = $x32 * 32 + $bit;
                        }
                    }
                }

                if ($x32 * 32 + 31 > $right) {
                    $bit = 31;

                    while (0 === BitUtils::unsignedrightshift($bits, $bit)) {
                        --$bit;
                    }

                    if (($x32 * 32 + $bit) > $right) {
                        $right = $x32 * 32 + $bit;
                    }
                }
            }
        }

        $width = $right - $left;
        $height = $bottom - $top;

        if ($width < 0 || $height < 0) {
            return null;
        }

        return [$left, $top, $width, $height];
    }

    /**
     * Gets the most top left set bit.
     *
     * This is useful in detecting a corner of a 'pure' barcode.
     *
     * @return int[]|null
     */
    public function gettopleftonbit(): ?array {
        $bitsoffset = 0;

        while ($bitsoffset < count($this->bits) && 0 === $this->bits[$bitsoffset]) {
            ++$bitsoffset;
        }

        if (count($this->bits) === $bitsoffset) {
            return null;
        }

        $x = intdiv($bitsoffset, $this->rowsize);
        $y = ($bitsoffset % $this->rowsize) << 5;

        $bits = $this->bits[$bitsoffset];
        $bit = 0;

        while (0 === ($bits << (31 - $bit))) {
            ++$bit;
        }

        $x += $bit;

        return [$x, $y];
    }

    /**
     * Gets the most bottom right set bit.
     *
     * This is useful in detecting a corner of a 'pure' barcode.
     *
     * @return int[]|null
     */
    public function getbottomrightonbit(): ?array {
        $bitsoffset = count($this->bits) - 1;

        while ($bitsoffset >= 0 && 0 === $this->bits[$bitsoffset]) {
            --$bitsoffset;
        }

        if ($bitsoffset < 0) {
            return null;
        }

        $x = intdiv($bitsoffset, $this->rowsize);
        $y = ($bitsoffset % $this->rowsize) << 5;

        $bits = $this->bits[$bitsoffset];
        $bit  = 0;

        while (0 === BitUtils::unsignedrightshift($bits, $bit)) {
            --$bit;
        }

        $x += $bit;

        return [$x, $y];
    }

    /**
     * Gets the width of the matrix,
     */
    public function getwidth(): int {
        return $this->width;
    }

    /**
     * Gets the height of the matrix.
     */
    public function getheight(): int {
        return $this->height;
    }
}
