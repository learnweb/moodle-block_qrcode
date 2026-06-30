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

use SplFixedArray;

/**
 * Block pair.
 *
 * @copyright  2017 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class BlockPair
{
    /**
     * Creates a new block pair.
     *
     * @param SplFixedArray<int> $databytes Data bytes in the block.
     * @param SplFixedArray<int> $errorcorrectionbytes Error correction bytes in the block.
     */
    public function __construct(
        /**
         * @var SplFixedArray
         */
        private readonly SplFixedArray $databytes,
        /**
         * @var SplFixedArray
         */
        private readonly SplFixedArray $errorcorrectionbytes
    ) {
    }

    /**
     * Gets the data bytes.
     *
     * @return SplFixedArray<int>
     */
    public function getdatabytes(): SplFixedArray {
        return $this->databytes;
    }

    /**
     * Gets the error correction bytes.
     *
     * @return SplFixedArray<int>
     */
    public function geterrorcorrectionbytes(): SplFixedArray {
        return $this->errorcorrectionbytes;
    }
}
