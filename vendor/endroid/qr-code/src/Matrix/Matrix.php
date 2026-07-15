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

namespace Endroid\QrCode\Matrix;

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Exception\BlockSizeTooSmallException;
use Endroid\QrCode\RoundBlockSizeMode;

/**
 * Represents a matrix of blocks for a QR code.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class Matrix implements MatrixInterface
{
    /**
     * @var float|int
     */
    private float $blocksize;
    /**
     * @var int
     */
    private int $innersize;
    /**
     * @var int|float
     */
    private int $outersize;
    /**
     * @var int
     */
    private int $marginleft;
    /**
     * @var int|float
     */
    private int $marginright;

    /**
     * Constructs a new Matrix instance with the given block values, size, margin, and round block size mode.
     *
     * @param array $blockvalues
     * @param int $size
     * @param int $margin
     * @param RoundBlockSizeMode $roundblocksizemode
     * @throws BlockSizeTooSmallException
     */
    public function __construct(
        /**
         * @var array
         */
        private array $blockvalues,
        int $size,
        int $margin,
        RoundBlockSizeMode $roundblocksizemode,
    ) {
        $blocksize = $size / $this->get_block_count();
        $innersize = $size;
        $outersize = $size + 2 * $margin;

        switch ($roundblocksizemode) {
            case RoundBlockSizeMode::Enlarge:
                $blocksize = intval(ceil($blocksize));
                $innersize = intval($blocksize * $this->get_block_count());
                $outersize = $innersize + 2 * $margin;
                break;
            case RoundBlockSizeMode::Shrink:
                $blocksize = intval(floor($blocksize));
                $innersize = intval($blocksize * $this->get_block_count());
                $outersize = $innersize + 2 * $margin;
                break;
            case RoundBlockSizeMode::Margin:
                $blocksize = intval(floor($blocksize));
                $innersize = intval($blocksize * $this->get_block_count());
                break;
        }

        if ($blocksize < 1) {
            throw new BlockSizeTooSmallException('Too much data: increase image dimensions or lower error correction level');
        }

        $this->blocksize = $blocksize;
        $this->innersize = $innersize;
        $this->outersize = $outersize;
        $this->marginleft = intval(($this->outersize - $this->innersize) / 2);
        $this->marginright = $this->outersize - $this->innersize - $this->marginleft;
    }

    /**
     * Returns the value of a block at the specified row and column indices.
     *
     * @param int $rowindex
     * @param int $columnindex
     * @return int
     */
    public function get_block_value(int $rowindex, int $columnindex): int {
        return $this->blockvalues[$rowindex][$columnindex];
    }

    /**
     * Returns the number of blocks in the matrix.
     *
     * @return int
     */
    public function get_block_count(): int {
        return count($this->blockvalues[0]);
    }

    /**
     * Returns the size of each block in the matrix.
     *
     * @return float
     */
    public function get_block_size(): float {
        return $this->blocksize;
    }

    /**
     * Returns the inner size of the matrix (excluding margins).
     *
     * @return int
     */
    public function get_inner_size(): int {
        return $this->innersize;
    }

    /**
     * Returns the outer size of the matrix (including margins).
     *
     * @return int
     */
    public function get_outer_size(): int {
        return $this->outersize;
    }

    /**
     * Returns the left margin size of the matrix.
     *
     * @return int
     */
    public function get_margin_left(): int {
        return $this->marginleft;
    }

    /**
     * Returns the right margin size of the matrix.
     *
     *
     * @return int
     */
    public function get_margin_right(): int {
        return $this->marginright;
    }
}
