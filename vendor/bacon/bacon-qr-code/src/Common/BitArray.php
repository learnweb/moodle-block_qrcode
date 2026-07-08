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
 * A simple, fast array of bits.
 *
 * @package    qrcode
 * @copyright  2017 T. Gunkel
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class BitArray
{
    /**
     * Bits represented as an array of integers.
     *
     * @var SplFixedArray<int>
     */
    private SplFixedArray $bits;

    /**
     * @var int
     */
    private int $size;

    /**
     * Creates a new bit array with a given size.
     */
    public function __construct($size = 0) {
        $this->size = $size;
        $this->bits = SplFixedArray::fromArray(array_fill(0, ($this->size + 31) >> 3, 0));
    }

    /**
     * Gets the size in bits.
     */
    public function get_size(): int {
        return $this->size;
    }

    /**
     * Gets the size in bytes.
     */
    public function get_size_in_bytes(): int {
        return ($this->size + 7) >> 3;
    }

    /**
     * Ensures that the array has a minimum capacity.
     */
    public function ensure_capacity(int $size): void {
        if ($size > count($this->bits) << 5) {
            $this->bits->setSize(($size + 31) >> 5);
        }
    }

    /**
     * Gets a specific bit.
     */
    public function get(int $i): bool {
        return 0 !== ($this->bits[$i >> 5] & (1 << ($i & 0x1f)));
    }

    /**
     * Sets a specific bit.
     */
    public function set(int $i): void {
        $this->bits[$i >> 5] = $this->bits[$i >> 5] | 1 << ($i & 0x1f);
    }

    /**
     * Flips a specific bit.
     */
    public function flip(int $i): void {
        $this->bits[$i >> 5] ^= 1 << ($i & 0x1f);
    }

    /**
     * Gets the next set bit position from a given position.
     */
    public function get_next_set(int $from): int {
        if ($from >= $this->size) {
            return $this->size;
        }

        $bitsoffset = $from >> 5;
        $currentbits = $this->bits[$bitsoffset];
        $bitslength = count($this->bits);
        $currentbits &= ~((1 << ($from & 0x1f)) - 1);

        while (0 === $currentbits) {
            if (++$bitsoffset === $bitslength) {
                return $this->size;
            }

            $currentbits = $this->bits[$bitsoffset];
        }

        $result = ($bitsoffset << 5) + BitUtils::numberoftrailingzeros($currentbits);
        return min($result, $this->size);
    }

    /**
     * Gets the next unset bit position from a given position.
     */
    public function get_next_unset(int $from): int {
        if ($from >= $this->size) {
            return $this->size;
        }

        $bitsoffset = $from >> 5;
        $currentbits = ~$this->bits[$bitsoffset];
        $bitslength = count($this->bits);
        $currentbits &= ~((1 << ($from & 0x1f)) - 1);

        while (0 === $currentbits) {
            if (++$bitsoffset === $bitslength) {
                return $this->size;
            }

            $currentbits = ~$this->bits[$bitsoffset];
        }

        $result = ($bitsoffset << 5) + BitUtils::numberoftrailingzeros($currentbits);
        return min($result, $this->size);
    }

    /**
     * Sets a bulk of bits.
     */
    public function set_bulk(int $i, int $newbits): void {
        $this->bits[$i >> 5] = $newbits;
    }

    /**
     * Sets a range of bits.
     *
     * @throws InvalidArgumentException if end is smaller than start
     */
    public function set_range(int $start, int $end): void {
        if ($end < $start) {
            throw new InvalidArgumentException('End must be greater or equal to start');
        }

        if ($end === $start) {
            return;
        }

        --$end;

        $firstint = $start >> 5;
        $lastint = $end >> 5;

        for ($i = $firstint; $i <= $lastint; ++$i) {
            $firstbit = $i > $firstint ? 0 : $start & 0x1f;
            $lastbit = $i < $lastint ? 31 : $end & 0x1f;

            if (0 === $firstbit && 31 === $lastbit) {
                $mask = 0x7fffffff;
            } else {
                $mask = 0;

                for ($j = $firstbit; $j < $lastbit; ++$j) {
                    $mask |= 1 << $j;
                }
            }

            $this->bits[$i] = $this->bits[$i] | $mask;
        }
    }

    /**
     * Clears the bit array, unsetting every bit.
     */
    public function clear(): void {
        $bitslength = count($this->bits);

        for ($i = 0; $i < $bitslength; ++$i) {
            $this->bits[$i] = 0;
        }
    }

    /**
     * Checks if a range of bits is set or not set.

     * @throws InvalidArgumentException if end is smaller than start
     */
    public function is_range(int $start, int $end, bool $value): bool {
        if ($end < $start) {
            throw new InvalidArgumentException('End must be greater or equal to start');
        }

        if ($end === $start) {
            return true;
        }

        --$end;

        $firstint = $start >> 5;
        $lastint = $end >> 5;

        for ($i = $firstint; $i <= $lastint; ++$i) {
            $firstbit = $i > $firstint ? 0 : $start & 0x1f;
            $lastbit = $i < $lastint ? 31 : $end & 0x1f;

            if (0 === $firstbit && 31 === $lastbit) {
                $mask = 0x7fffffff;
            } else {
                $mask = 0;

                for ($j = $firstbit; $j <= $lastbit; ++$j) {
                    $mask |= 1 << $j;
                }
            }

            if (($this->bits[$i] & $mask) !== ($value ? $mask : 0)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Appends a bit to the array.
     */
    public function append_bit(bool $bit): void {
        $this->ensure_capacity($this->size + 1);

        if ($bit) {
            $this->bits[$this->size >> 5] = $this->bits[$this->size >> 5] | (1 << ($this->size & 0x1f));
        }

        ++$this->size;
    }

    /**
     * Appends a number of bits (up to 32) to the array.

     * @throws InvalidArgumentException if num bits is not between 0 and 32
     */
    public function append_bits(int $value, int $numbits): void {
        if ($numbits < 0 || $numbits > 32) {
            throw new InvalidArgumentException('Num bits must be between 0 and 32');
        }

        $this->ensure_capacity($this->size + $numbits);

        for ($numbitsleft = $numbits; $numbitsleft > 0; $numbitsleft--) {
            $this->append_bit((($value >> ($numbitsleft - 1)) & 0x01) === 1);
        }
    }

    /**
     * Appends another bit array to this array.
     */
    public function append_bit_array(self $other): void {
        $othersize = $other->get_size();
        $this->ensure_capacity($this->size + $other->get_size());

        for ($i = 0; $i < $othersize; ++$i) {
            $this->append_bit($other->get($i));
        }
    }

    /**
     * Makes an exclusive-or comparision on the current bit array.
     *
     * @throws InvalidArgumentException if sizes don't match
     */
    public function xor_bits(self $other): void {
        $bitslength = count($this->bits);
        $otherbits  = $other->get_bit_array();

        if ($bitslength !== count($otherbits)) {
            throw new InvalidArgumentException('Sizes don\'t match');
        }

        for ($i = 0; $i < $bitslength; ++$i) {
            $this->bits[$i] = $this->bits[$i] ^ $otherbits[$i];
        }
    }

    /**
     * Converts the bit array to a byte array.
     *
     * @return SplFixedArray<int>
     */
    public function to_bytes(int $bitoffset, int $numbytes): SplFixedArray {
        $bytes = new SplFixedArray($numbytes);

        for ($i = 0; $i < $numbytes; ++$i) {
            $byte = 0;

            for ($j = 0; $j < 8; ++$j) {
                if ($this->get($bitoffset)) {
                    $byte |= 1 << (7 - $j);
                }

                ++$bitoffset;
            }

            $bytes[$i] = $byte;
        }

        return $bytes;
    }

    /**
     * Gets the internal bit array.
     *
     * @return SplFixedArray<int>
     */
    public function get_bit_array(): SplFixedArray {
        return $this->bits;
    }

    /**
     * Reverses the array.
     */
    public function reverse(): void {
        $newbits = new SplFixedArray(count($this->bits));

        for ($i = 0; $i < $this->size; ++$i) {
            if ($this->get($this->size - $i - 1)) {
                $newbits[$i >> 5] = $newbits[$i >> 5] | (1 << ($i & 0x1f));
            }
        }

        $this->bits = $newbits;
    }

    /**
     * Returns a string representation of the bit array.
     */
    public function __toString(): string {
        $result = '';

        for ($i = 0; $i < $this->size; ++$i) {
            if (0 === ($i & 0x07)) {
                $result .= ' ';
            }

            $result .= $this->get($i) ? 'X' : '.';
        }

        return $result;
    }
}
