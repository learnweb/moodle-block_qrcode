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

defined('MOODLE_INTERNAL') || die();

/**
 * Represents the margin around a label in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class Margin implements MarginInterface
{
    /**
     * Constructor.
     *
     * @param int $top
     * @param int $right
     * @param int $bottom
     * @param int $left
     */
    public function __construct(
        /**
         * @var int
         */
        private int $top,
        /**
         * @var int
         */
        private int $right,
        /**
         * @var int
         */
        private int $bottom,
        /**
         * @var int
         */
        private int $left,
    ) {
    }

    /**
     * Returns the top margin.
     *
     * @return int
     */
    public function get_top(): int {
        return $this->top;
    }

    /**
     * Returns the right margin.
     *
     * @return int
     */
    public function get_right(): int {
        return $this->right;
    }

    /**
     * Returns the bottom margin.
     *
     * @return int
     */
    public function get_bottom(): int {
        return $this->bottom;
    }

    /**
     * Returns the left margin.
     *
     * @return int
     */
    public function get_left(): int {
        return $this->left;
    }

    /**
     * Returns the margin values as an associative array.
     *
     * @return array|int[]
     */
    public function to_array(): array {
        return [
            'top' => $this->top,
            'right' => $this->right,
            'bottom' => $this->bottom,
            'left' => $this->left,
        ];
    }
}
