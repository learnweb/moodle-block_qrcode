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

/**
 * Interface for a matrix of blocks for a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface MatrixInterface
{
    /**
     * Returns the value of the block at the specified row and column indices.
     *
     * @param int $rowindex
     * @param int $columnindex
     * @return int
     */
    public function get_block_value(int $rowindex, int $columnindex): int;

    /**
     * Returns the number of blocks in the matrix.
     *
     * @return int
     */
    public function get_block_count(): int;

    /**
     * Returns the size of each block in the matrix.
     *
     * @return float
     */
    public function get_block_size(): float;

    /**
     * Returns the inner size of the matrix, which is the number of blocks in the matrix without the margins.
     *
     * @return int
     */
    public function get_inner_size(): int;

    /**
     * Returns the outer size of the matrix, which is the number of blocks in the matrix including the margins.
     *
     * @return int
     */
    public function get_outer_size(): int;

    /**
     * Returns the left margin of the matrix, which is the number of blocks to the left of the inner matrix.
     *
     * @return int
     */
    public function get_margin_left(): int;

    /**
     * Returns the right margin of the matrix, which is the number of blocks to the right of the inner matrix.
     *
     * @return int
     */
    public function get_margin_right(): int;
}
