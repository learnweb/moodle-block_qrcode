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
 * @copyright 2025 D. Meißner
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Curve implements OperationInterface
{
    /**
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $x3
     * @param float $y3
     */
    public function __construct(
            /**
             * @var float
             */
        private readonly float $x1,
            /**
             * @var float
             */
        private readonly float $y1,
            /**
             * @var float
             */
        private readonly float $x2,
            /**
             * @var float
             */
        private readonly float $y2,
            /**
             * @var float
             */
        private readonly float $x3,
            /**
             * @var float
             */
        private readonly float $y3
    ) {
    }

    /**
     * Getter for x1.
     * @return float
     */
    public function get_x_1(): float {
        return $this->x1;
    }

    /**
     * Getter for y1.
     * @return float
     */
    public function get_y_1(): float {
        return $this->y1;
    }

    /**
     * Getter for x2.
     * @return float
     */
    public function get_x_2(): float {
        return $this->x2;
    }

    /**
     * Getter for y2.
     * @return float
     */
    public function get_y_2(): float {
        return $this->y2;
    }

    /**
     * Getter for x3.
     * @return float
     */
    public function get_x_3(): float {
        return $this->x3;
    }

    /**
     * Getter for y3.
     * @return float
     */
    public function get_y_3(): float {
        return $this->y3;
    }

    /**
     * @param float $x
     * @param float $y
     * @return OperationInterface
     */
    public function translate(float $x, float $y): OperationInterface {
        return new self(
            $this->x1 + $x,
            $this->y1 + $y,
            $this->x2 + $x,
            $this->y2 + $y,
            $this->x3 + $x,
            $this->y3 + $y
        );
    }

    /**
     * @param int $degrees
     * @return OperationInterface
     */
    public function rotate(int $degrees): OperationInterface {
        $radians = deg2rad($degrees);
        $sin = sin($radians);
        $cos = cos($radians);
        $x1r = $this->x1 * $cos - $this->y1 * $sin;
        $y1r = $this->x1 * $sin + $this->y1 * $cos;
        $x2r = $this->x2 * $cos - $this->y2 * $sin;
        $y2r = $this->x2 * $sin + $this->y2 * $cos;
        $x3r = $this->x3 * $cos - $this->y3 * $sin;
        $y3r = $this->x3 * $sin + $this->y3 * $cos;
        return new self(
            $x1r,
            $y1r,
            $x2r,
            $y2r,
            $x3r,
            $y3r
        );
    }
}
