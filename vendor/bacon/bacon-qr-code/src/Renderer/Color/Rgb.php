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

final class Rgb implements ColorInterface
{
    /**
     * @param int $red the red amount of the color, 0 to 255
     * @param int $green the green amount of the color, 0 to 255
     * @param int $blue the blue amount of the color, 0 to 255
     */
    public function __construct(private readonly int $red, private readonly int $green, private readonly int $blue) {
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

    public function getRed(): int {
        return $this->red;
    }

    public function getGreen(): int {
        return $this->green;
    }

    public function getBlue(): int {
        return $this->blue;
    }

    public function to_rgb(): Rgb {
        return $this;
    }

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

    public function to_gray(): Gray {
        return new Gray((int) (($this->red * 0.21 + $this->green * 0.71 + $this->blue * 0.07) / 2.55));
    }
}
