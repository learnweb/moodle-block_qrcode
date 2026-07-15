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
 * Alpha color wrapper.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Alpha implements ColorInterface
{
    /**
     * Constructor.
     *
     * @param int $alpha the alpha value, 0 to 100
     */
    public function __construct(
        /**
         * @var int
         */
        private readonly int $alpha,
        /**
         * @var ColorInterface
         */
        private readonly ColorInterface $basecolor
    ) {
        if ($alpha < 0 || $alpha > 100) {
            throw new Exception\InvalidArgumentException('Alpha must be between 0 and 100');
        }
    }

    /**
     * Returns the alpha value.
     *
     * @return int
     */
    public function get_alpha(): int {
        return $this->alpha;
    }

    /**
     * Returns the base color.
     *
     * @return ColorInterface
     */
    public function get_base_color(): ColorInterface {
        return $this->basecolor;
    }

    /**
     * Converts this color to RGB.
     *
     * @return Rgb
     */
    public function to_rgb(): Rgb {
        return $this->basecolor->to_rgb();
    }

    /**
     * Converts this color to CMYK.
     *
     * @return Cmyk
     */
    public function to_cmyk(): Cmyk {
        return $this->basecolor->to_cmyk();
    }

    /**
     * Converts this color to gray.
     *
     * @return Gray
     */
    public function to_gray(): Gray {
        return $this->basecolor->to_gray();
    }
}
