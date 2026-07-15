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

namespace Endroid\QrCode\Label\Margin;

/**
 * Interface for a margin around a label in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface MarginInterface
{
    /**
     * Returns the top margin.
     *
     * @return int
     */
    public function get_top(): int;

    /**
     * Returns the right margin.
     *
     * @return int
     */
    public function get_right(): int;

    /**
     * Returns the bottom margin.
     *
     * @return int
     */
    public function get_bottom(): int;

    /**
     * Returns the left margin.
     *
     * @return int
     */
    public function get_left(): int;

    /**
     * Returns the margin as an array with keys 'top', 'right', 'bottom', and 'left'.
     *
     * @return array
     */
    public function to_array(): array;
}
