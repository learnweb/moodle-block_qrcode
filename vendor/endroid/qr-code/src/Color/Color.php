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

final readonly class Color implements ColorInterface
{
    public function __construct(
        private int $red,
        private int $green,
        private int $blue,
        private int $alpha = 0,
    ) {
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

    public function getAlpha(): int {
        return $this->alpha;
    }

    public function getOpacity(): float {
        return 1 - $this->alpha / 127;
    }

    public function getHex(): string {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    public function toArray(): array {
        return [
            'red' => $this->red,
            'green' => $this->green,
            'blue' => $this->blue,
            'alpha' => $this->alpha,
        ];
    }
}
