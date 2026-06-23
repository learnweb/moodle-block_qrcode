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

namespace BaconQrCode\Renderer\Path;

final class Move implements OperationInterface
{
    public function __construct(private readonly float $x, private readonly float $y) {
    }

    public function getX(): float {
        return $this->x;
    }

    public function getY(): float {
        return $this->y;
    }

    /**
     * @return self
     */
    public function translate(float $x, float $y): OperationInterface {
        return new self($this->x + $x, $this->y + $y);
    }

    /**
     * @return self
     */
    public function rotate(int $degrees): OperationInterface {
        $radians = deg2rad($degrees);
        $sin = sin($radians);
        $cos = cos($radians);
        $xr = $this->x * $cos - $this->y * $sin;
        $yr = $this->x * $sin + $this->y * $cos;
        return new self($xr, $yr);
    }
}
