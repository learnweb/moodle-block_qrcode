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

namespace Endroid\QrCode\Color;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a color in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class Color implements ColorInterface
{
    /**
     * Constructor.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     */
    public function __construct(
        /**
         * @var int
         */
        private int $red,
        /**
         * @var int
         */
        private int $green,
        /**
         * @var int
         */
        private int $blue,
        /**
         * @var int
         */
        private int $alpha = 0,
    ) {
    }

    /**
     * Returns the red component of the color (0-255).
     *
     * @return int
     */
    public function get_red(): int {
        return $this->red;
    }

    /**
     * Returns the green component of the color (0-255).
     *
     * @return int
     */
    public function get_green(): int {
        return $this->green;
    }

    /**
     * Returns the blue component of the color (0-255).
     *
     * @return int
     */
    public function get_blue(): int {
        return $this->blue;
    }

    /**
     * Returns the alpha component of the color (0-255).
     *
     * @return int
     */
    public function get_alpha(): int {
        return $this->alpha;
    }

    /**
     * Returns the opacity of the color (0.0-1.0).
     *
     * @return float
     */
    public function get_opacity(): float {
        return 1 - $this->alpha / 127;
    }

    /**
     * Returns the hexadecimal representation of the color (e.g., #RRGGBB or #RRGGBBAA).
     *
     * @return string
     */
    public function get_hex(): string {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    /**
     * Returns the color as an array with keys 'red', 'green', 'blue', and 'alpha'.
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'red' => $this->red,
            'green' => $this->green,
            'blue' => $this->blue,
            'alpha' => $this->alpha,
        ];
    }
}
