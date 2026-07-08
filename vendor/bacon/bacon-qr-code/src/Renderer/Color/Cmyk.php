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
 * CMYK color representation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Cmyk implements ColorInterface
{
    /**
     * Constructor.
     *
     * @param int $cyan the cyan amount, 0 to 100
     * @param int $magenta the magenta amount, 0 to 100
     * @param int $yellow the yellow amount, 0 to 100
     * @param int $black the black amount, 0 to 100
     */
    public function __construct(
        /**
         * @var int
         */
        private readonly int $cyan,
        /**
         * @var int
         */
        private readonly int $magenta,
        /**
         * @var int
         */
        private readonly int $yellow,
        /**
         * @var int
         */
        private readonly int $black
    ) {
        if ($cyan < 0 || $cyan > 100) {
            throw new Exception\InvalidArgumentException('Cyan must be between 0 and 100');
        }

        if ($magenta < 0 || $magenta > 100) {
            throw new Exception\InvalidArgumentException('Magenta must be between 0 and 100');
        }

        if ($yellow < 0 || $yellow > 100) {
            throw new Exception\InvalidArgumentException('Yellow must be between 0 and 100');
        }

        if ($black < 0 || $black > 100) {
            throw new Exception\InvalidArgumentException('Black must be between 0 and 100');
        }
    }

    /**
     * Returns the cyan amount.
     *
     * @return int
     */
    public function get_cyan(): int {
        return $this->cyan;
    }

    /**
     * Returns the magenta amount.
     *
     * @return int
     */
    public function get_magenta(): int {
        return $this->magenta;
    }

    /**
     * Returns the yellow amount.
     *
     * @return int
     */
    public function get_yellow(): int {
        return $this->yellow;
    }

    /**
     * Returns the black amount.
     *
     * @return int
     */
    public function get_black(): int {
        return $this->black;
    }

    /**
     * Converts this color to RGB.
     *
     * @return Rgb
     */
    public function to_rgb(): Rgb {
        $k = $this->black / 100;
        $c = (-$k * $this->cyan + $k * 100 + $this->cyan) / 100;
        $m = (-$k * $this->magenta + $k * 100 + $this->magenta) / 100;
        $y = (-$k * $this->yellow + $k * 100 + $this->yellow) / 100;

        return new Rgb(
            (int) (-$c * 255 + 255),
            (int) (-$m * 255 + 255),
            (int) (-$y * 255 + 255)
        );
    }

    /**
     * Converts this color to CMYK.
     *
     * @return $this
     */
    public function to_cmyk(): Cmyk {
        return $this;
    }

    /**
     * Converts this color to gray.
     *
     * @return Gray
     */
    public function to_gray(): Gray {
        return $this->to_rgb()->to_gray();
    }
}
