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

final class Alpha implements ColorInterface
{
    /**
     * @param int $alpha the alpha value, 0 to 100
     */
    public function __construct(private readonly int $alpha, private readonly ColorInterface $baseColor) {
        if ($alpha < 0 || $alpha > 100) {
            throw new Exception\InvalidArgumentException('Alpha must be between 0 and 100');
        }
    }

    public function getAlpha(): int {
        return $this->alpha;
    }

    public function getBaseColor(): ColorInterface {
        return $this->baseColor;
    }

    public function to_rgb(): Rgb {
        return $this->baseColor->to_rgb();
    }

    public function to_cmyk(): Cmyk {
        return $this->baseColor->to_cmyk();
    }

    public function to_gray(): Gray {
        return $this->baseColor->to_gray();
    }
}
