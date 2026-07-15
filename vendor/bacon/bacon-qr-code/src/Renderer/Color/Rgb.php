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

namespace BaconQrCode\Renderer\Color;

use BaconQrCode\Exception;

/**
 * RGB color representation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Rgb implements ColorInterface
{
    /**
     * Constructor.
     * @param int $red the red amount of the color, 0 to 255
     * @param int $green the green amount of the color, 0 to 255
     * @param int $blue the blue amount of the color, 0 to 255
     */
    public function __construct(
        /**
         * @var int
         */
        private readonly int $red,
        /**
         * @var int
         */
        private readonly int $green,
        /**
         * @var int
         */
        private readonly int $blue
    ) {
        if ($red < 0 || $red > 255) {
            throw new Exception\InvalidArgumentException('Red must be between 0 and 255');
        }

        if ($green < 0 || $green > 255) {
            throw new Exception\InvalidArgumentException('Green must be between 0 and 255');
        }

        if ($blue < 0 || $blue > 255) {
            throw new Exception\InvalidArgumentException('Blue must be between 0 and 255');
        }
    }

    /**
     * Returns the red amount.
     *
     * @return int
     */
    public function get_red(): int {
        return $this->red;
    }

    /**
     * Returns the green amount.
     *
     * @return int
     */
    public function get_green(): int {
        return $this->green;
    }

    /**
     * Returns the blue amount.
     *
     * @return int
     */
    public function get_blue(): int {
        return $this->blue;
    }

    /**
     * Converts this color to RGB.
     *
     * @return $this
     */
    public function to_rgb(): Rgb {
        return $this;
    }

    /**
     * Converts this color to CMYK.
     *
     * @return Cmyk
     */
    public function to_cmyk(): Cmyk {
        $c = 1 - ($this->red / 255);
        $m = 1 - ($this->green / 255);
        $y = 1 - ($this->blue / 255);
        $k = min($c, $m, $y);

        if ($k === 0) {
            return new Cmyk(0, 0, 0, 0);
        }

        return new Cmyk(
            (int) (100 * ($c - $k) / (1 - $k)),
            (int) (100 * ($m - $k) / (1 - $k)),
            (int) (100 * ($y - $k) / (1 - $k)),
            (int) (100 * $k)
        );
    }

    /**
     * Converts this color to gray.
     *
     * @return Gray
     */
    public function to_gray(): Gray {
        return new Gray((int) (($this->red * 0.21 + $this->green * 0.71 + $this->blue * 0.07) / 2.55));
    }
}
