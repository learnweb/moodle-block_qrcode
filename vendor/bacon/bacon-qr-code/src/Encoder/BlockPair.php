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
 */
final class BlockPair
{
    /**
     * Creates a new block pair.
     *
     * @param SplFixedArray<int> $dataBytes Data bytes in the block.
     * @param SplFixedArray<int> $errorCorrectionBytes Error correction bytes in the block.
     */
    public function __construct(
        private readonly SplFixedArray $dataBytes,
        private readonly SplFixedArray $errorCorrectionBytes
    ) {
    }

    /**
     * Gets the data bytes.
     *
     * @return SplFixedArray<int>
     */
    public function getDataBytes(): SplFixedArray {
        return $this->dataBytes;
    }

    /**
     * Gets the error correction bytes.
     *
     * @return SplFixedArray<int>
     */
    public function getErrorCorrectionBytes(): SplFixedArray {
        return $this->errorCorrectionBytes;
    }
}
