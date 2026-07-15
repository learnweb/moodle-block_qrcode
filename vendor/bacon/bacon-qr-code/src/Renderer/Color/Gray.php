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
 * Gray color representation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Gray implements ColorInterface
{
    /**
     * Constructor.
     *
     * @param int $gray the gray value between 0 (black) and 100 (white)
     */
    public function __construct(
        /**
         * @var int
         */
        private readonly int $gray
    ) {
        if ($gray < 0 || $gray > 100) {
            throw new Exception\InvalidArgumentException('Gray must be between 0 and 100');
        }
    }

    /**
     * Returns the gray value.
     *
     * @return int
     */
    public function get_gray(): int {
        return $this->gray;
    }

    /**
     * Converts the gray color to RGB format.
     *
     * @return Rgb
     */
    public function to_rgb(): Rgb {
        return new Rgb((int) ($this->gray * 2.55), (int) ($this->gray * 2.55), (int) ($this->gray * 2.55));
    }

    /**
     * Converts the gray color to CMYK format.
     *
     * @return Cmyk
     */
    public function to_cmyk(): Cmyk {
        return new Cmyk(0, 0, 0, 100 - $this->gray);
    }

    /**
     * Converts the gray color to Gray format.
     *
     * @return $this
     */
    public function to_gray(): Gray {
        return $this;
    }
}
