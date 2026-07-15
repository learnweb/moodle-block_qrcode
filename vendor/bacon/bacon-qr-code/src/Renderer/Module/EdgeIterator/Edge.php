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

/**
 * Edge representation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Edge
{
    /**
     * @var array<int[]>
     */
    private array $points = [];

    /**
     * @var array<int[]>|null
     */
    private ?array $simplifiedpoints = null;

    /**
     * @var int
     */
    private int $minx = PHP_INT_MAX;

    /**
     * @var int
     */
    private int $miny = PHP_INT_MAX;

    /**
     * @var int
     */
    private int $maxx = -1;

    /**
     * @var int
     */
    private int $maxy = -1;

    /**
     * Constructor.
     * @param bool $positive
     */
    public function __construct(
        /**
         * @var bool
         */
        private readonly bool $positive
    ) {
    }

    /**
     * Adds a point to the edge.
     *
     * @param int $x
     * @param int $y
     * @return void
     */
    public function add_point(int $x, int $y): void {
        $this->points[] = [$x, $y];
        $this->minx = min($this->minx, $x);
        $this->miny = min($this->miny, $y);
        $this->maxx = max($this->maxx, $x);
        $this->maxy = max($this->maxy, $y);
    }

    /**
     * Returns whether the edge is positive.
     *
     * @return bool
     */
    public function is_positive(): bool {
        return $this->positive;
    }

    /**
     * Returns the points of the edge.
     *
     * @return array<int[]>
     */
    public function get_points(): array {
        return $this->points;
    }

    /**
     * Returns the minimum x-coordinate of the edge.
     *
     * @return int
     */
    public function get_max_x(): int {
        return $this->maxx;
    }

    /**
     * Returns the minimum y-coordinate of the edge.
     *
     * @return array|\int[][]
     */
    public function get_simplified_points(): array {
        if (null !== $this->simplifiedpoints) {
            return $this->simplifiedpoints;
        }

        $points = [];
        $length = count($this->points);

        for ($i = 0; $i < $length; ++$i) {
            $previouspoint = $this->points[(0 === $i ? $length : $i) - 1];
            $nextpoint = $this->points[($length - 1 === $i ? -1 : $i) + 1];
            $currentpoint = $this->points[$i];

            if (
                ($previouspoint[0] === $currentpoint[0] && $currentpoint[0] === $nextpoint[0])
                || ($previouspoint[1] === $currentpoint[1] && $currentpoint[1] === $nextpoint[1])
            ) {
                continue;
            }

            $points[] = $currentpoint;
        }

        return $this->simplifiedpoints = $points;
    }
}
