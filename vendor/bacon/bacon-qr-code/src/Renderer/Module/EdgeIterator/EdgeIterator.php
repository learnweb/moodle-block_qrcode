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

use BaconQrCode\Encoder\ByteMatrix;
use IteratorAggregate;
use Traversable;

/**
 * Edge iterator based on potrace.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EdgeIterator implements IteratorAggregate
{
    /**
     * @var int[]
     */
    private array $bytes = [];

    /**
     * @var int|null
     */
    private ?int $size;

    /**
     * @var int
     */
    private int $width;

    /**
     * @var int
     */
    private int $height;

    /**
     * Constructor.
     *
     * @param ByteMatrix $matrix
     */
    public function __construct(ByteMatrix $matrix) {
        $this->bytes = iterator_to_array($matrix->get_bytes());
        $this->size = count($this->bytes);
        $this->width = $matrix->get_width();
        $this->height = $matrix->get_height();
    }

    /**
     * Returns the edge iterator.
     *
     * @return Traversable<Edge>
     */
    public function getIterator(): Traversable {
        $originalbytes = $this->bytes;
        $point = $this->find_next(0, 0);

        while (null !== $point) {
            $edge = $this->find_edge($point[0], $point[1]);
            $this->xor_edge($edge);

            yield $edge;

            $point = $this->find_next($point[0], $point[1]);
        }

        $this->bytes = $originalbytes;
    }

    /**
     * Finds the next set point.
     *
     * @return int[]|null
     */
    private function find_next(int $x, int $y): ?array {
        $i = $this->width * $y + $x;

        while ($i < $this->size && 1 !== $this->bytes[$i]) {
            ++$i;
        }

        if ($i < $this->size) {
            return $this->point_of($i);
        }

        return null;
    }

    /**
     * Finds an edge from the given point.
     *
     * @param int $x
     * @param int $y
     * @return Edge
     */
    private function find_edge(int $x, int $y): Edge {
        $edge = new Edge($this->is_set($x, $y));
        $startx = $x;
        $starty = $y;
        $dirx = 0;
        $diry = 1;

        while (true) {
            $edge->addPoint($x, $y);
            $x += $dirx;
            $y += $diry;

            if ($x === $startx && $y === $starty) {
                break;
            }

            $left = $this->is_set($x + ($dirx + $diry - 1 ) / 2, $y + ($diry - $dirx - 1) / 2);
            $right = $this->is_set($x + ($dirx - $diry - 1) / 2, $y + ($diry + $dirx - 1) / 2);

            if ($right && ! $left) {
                $tmp = $dirx;
                $dirx = -$diry;
                $diry = $tmp;
            } else if ($right) {
                $tmp = $dirx;
                $dirx = -$diry;
                $diry = $tmp;
            } else if (! $left) {
                $tmp = $dirx;
                $dirx = $diry;
                $diry = -$tmp;
            }
        }

        return $edge;
    }

    /**
     * Applies XOR to the given edge.
     *
     * @param Edge $path
     * @return void
     */
    private function xor_edge(Edge $path): void {
        $points = $path->getPoints();
        $y1 = $points[0][1];
        $length = count($points);
        $maxx = $path->getMaxX();

        for ($i = 1; $i < $length; ++$i) {
            $y = $points[$i][1];

            if ($y === $y1) {
                continue;
            }

            $x = $points[$i][0];
            $miny = min($y1, $y);

            for ($j = $x; $j < $maxx; ++$j) {
                $this->flip($j, $miny);
            }

            $y1 = $y;
        }
    }

    /**
     * Checks whether the given point is set.
     *
     * @param int $x
     * @param int $y
     * @return bool
     */
    private function is_set(int $x, int $y): bool {
        return (
            $x >= 0
            && $x < $this->width
            && $y >= 0
            && $y < $this->height
        ) && 1 === $this->bytes[$this->width * $y + $x];
    }

    /**
     * Returns the point for the given index.
     *
     * @return int[]
     */
    private function point_of(int $i): array {
        $y = intdiv($i, $this->width);
        return [$i - $y * $this->width, $y];
    }

    /**
     * Flips the value at the given point.
     *
     * @param int $x
     * @param int $y
     * @return void
     */
    private function flip(int $x, int $y): void {
        $this->bytes[$this->width * $y + $x] = (
            $this->is_set($x, $y) ? 0 : 1
        );
    }
}
