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

/**
 * Represents a line path operation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Line implements OperationInterface
{
    /**
     * @var float
     */
    private readonly float $x;
    /**
     * @var float
     */
    private readonly float $y;
    /**
     * Constructor.
     *
     * @param float $x
     * @param float $y
     */
    public function __construct($x, $y) {
    }

    /**
     * Returns the X value.
     *
     * @return float
     */
    public function get_x(): float {
        return $this->x;
    }

    /**
     * Returns the Y value.
     *
     * @return float
     */
    public function get_y(): float {
        return $this->y;
    }

    /**
     * Returns a new line operation translated by the given X and Y values.
     *
     * @param float $x
     * @param float $y
     * @return OperationInterface
     */
    public function translate(float $x, float $y): OperationInterface {
        return new self($this->x + $x, $this->y + $y);
    }

    /**
     * Returns a rotated line operation.
     *
     * @param int $degrees
     * @return OperationInterface
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
