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

namespace BaconQrCode\Renderer\Module\EdgeIterator;

final class Edge
{
    /**
     * @var array<int[]>
     */
    private array $points = [];

    /**
     * @var array<int[]>|null
     */
    private ?array $simplifiedPoints = null;

    private int $minX = PHP_INT_MAX;

    private int $minY = PHP_INT_MAX;

    private int $maxX = -1;

    private int $maxY = -1;

    public function __construct(private readonly bool $positive) {
    }

    public function addPoint(int $x, int $y): void {
        $this->points[] = [$x, $y];
        $this->minX = min($this->minX, $x);
        $this->minY = min($this->minY, $y);
        $this->maxX = max($this->maxX, $x);
        $this->maxY = max($this->maxY, $y);
    }

    public function isPositive(): bool {
        return $this->positive;
    }

    /**
     * @return array<int[]>
     */
    public function getPoints(): array {
        return $this->points;
    }

    public function getMaxX(): int {
        return $this->maxX;
    }

    public function getSimplifiedPoints(): array {
        if (null !== $this->simplifiedPoints) {
            return $this->simplifiedPoints;
        }

        $points = [];
        $length = count($this->points);

        for ($i = 0; $i < $length; ++$i) {
            $previousPoint = $this->points[(0 === $i ? $length : $i) - 1];
            $nextPoint = $this->points[($length - 1 === $i ? -1 : $i) + 1];
            $currentPoint = $this->points[$i];

            if (
                ($previousPoint[0] === $currentPoint[0] && $currentPoint[0] === $nextPoint[0])
                || ($previousPoint[1] === $currentPoint[1] && $currentPoint[1] === $nextPoint[1])
            ) {
                continue;
            }

            $points[] = $currentPoint;
        }

        return $this->simplifiedPoints = $points;
    }
}
