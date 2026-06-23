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
 */
final class EdgeIterator implements IteratorAggregate
{
    /**
     * @var int[]
     */
    private array $bytes = [];

    private ?int $size;

    private int $width;

    private int $height;

    public function __construct(ByteMatrix $matrix) {
        $this->bytes = iterator_to_array($matrix->getBytes());
        $this->size = count($this->bytes);
        $this->width = $matrix->getWidth();
        $this->height = $matrix->getHeight();
    }

    /**
     * @return Traversable<Edge>
     */
    public function getIterator(): Traversable {
        $originalBytes = $this->bytes;
        $point = $this->findNext(0, 0);

        while (null !== $point) {
            $edge = $this->findEdge($point[0], $point[1]);
            $this->xorEdge($edge);

            yield $edge;

            $point = $this->findNext($point[0], $point[1]);
        }

        $this->bytes = $originalBytes;
    }

    /**
     * @return int[]|null
     */
    private function findNext(int $x, int $y): ?array {
        $i = $this->width * $y + $x;

        while ($i < $this->size && 1 !== $this->bytes[$i]) {
            ++$i;
        }

        if ($i < $this->size) {
            return $this->pointOf($i);
        }

        return null;
    }

    private function findEdge(int $x, int $y): Edge {
        $edge = new Edge($this->isSet($x, $y));
        $startX = $x;
        $startY = $y;
        $dirX = 0;
        $dirY = 1;

        while (true) {
            $edge->addPoint($x, $y);
            $x += $dirX;
            $y += $dirY;

            if ($x === $startX && $y === $startY) {
                break;
            }

            $left = $this->isSet($x + ($dirX + $dirY - 1 ) / 2, $y + ($dirY - $dirX - 1) / 2);
            $right = $this->isSet($x + ($dirX - $dirY - 1) / 2, $y + ($dirY + $dirX - 1) / 2);

            if ($right && ! $left) {
                $tmp = $dirX;
                $dirX = -$dirY;
                $dirY = $tmp;
            } else if ($right) {
                $tmp = $dirX;
                $dirX = -$dirY;
                $dirY = $tmp;
            } else if (! $left) {
                $tmp = $dirX;
                $dirX = $dirY;
                $dirY = -$tmp;
            }
        }

        return $edge;
    }

    private function xorEdge(Edge $path): void {
        $points = $path->getPoints();
        $y1 = $points[0][1];
        $length = count($points);
        $maxX = $path->getMaxX();

        for ($i = 1; $i < $length; ++$i) {
            $y = $points[$i][1];

            if ($y === $y1) {
                continue;
            }

            $x = $points[$i][0];
            $minY = min($y1, $y);

            for ($j = $x; $j < $maxX; ++$j) {
                $this->flip($j, $minY);
            }

            $y1 = $y;
        }
    }

    private function isSet(int $x, int $y): bool {
        return (
            $x >= 0
            && $x < $this->width
            && $y >= 0
            && $y < $this->height
        ) && 1 === $this->bytes[$this->width * $y + $x];
    }

    /**
     * @return int[]
     */
    private function pointOf(int $i): array {
        $y = intdiv($i, $this->width);
        return [$i - $y * $this->width, $y];
    }

    private function flip(int $x, int $y): void {
        $this->bytes[$this->width * $y + $x] = (
            $this->isSet($x, $y) ? 0 : 1
        );
    }
}
