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

use IteratorAggregate;
use Traversable;

/**
 * Internal Representation of a vector path.
 */
final class Path implements IteratorAggregate
{
    /**
     * @var OperationInterface[]
     */
    private array $operations = [];

    /**
     * Moves the drawing operation to a certain position.
     */
    public function move(float $x, float $y): self {
        $path = clone $this;
        $path->operations[] = new Move($x, $y);
        return $path;
    }

    /**
     * Draws a line from the current position to another position.
     */
    public function line(float $x, float $y): self {
        $path = clone $this;
        $path->operations[] = new Line($x, $y);
        return $path;
    }

    /**
     * Draws an elliptic arc from the current position to another position.
     */
    public function ellipticArc(
        float $xRadius,
        float $yRadius,
        float $xAxisRotation,
        bool $largeArc,
        bool $sweep,
        float $x,
        float $y
    ): self {
        $path = clone $this;
        $path->operations[] = new EllipticArc($xRadius, $yRadius, $xAxisRotation, $largeArc, $sweep, $x, $y);
        return $path;
    }

    /**
     * Draws a curve from the current position to another position.
     */
    public function curve(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): self {
        $path = clone $this;
        $path->operations[] = new Curve($x1, $y1, $x2, $y2, $x3, $y3);
        return $path;
    }

    /**
     * Closes a sub-path.
     */
    public function close(): self {
        $path = clone $this;
        $path->operations[] = Close::instance();
        return $path;
    }

    /**
     * Appends another path to this one.
     */
    public function append(self $other): self {
        $path = clone $this;
        $path->operations = array_merge($this->operations, $other->operations);
        return $path;
    }

    public function translate(float $x, float $y): self {
        $path = new self();

        foreach ($this->operations as $operation) {
            $path->operations[] = $operation->translate($x, $y);
        }

        return $path;
    }

    public function rotate(int $degrees): self {
        $path = new self();

        foreach ($this->operations as $operation) {
            $path->operations[] = $operation->rotate($degrees);
        }

        return $path;
    }

    /**
     * @return Traversable<int, OperationInterface>
     */
    public function getIterator(): Traversable {
        foreach ($this->operations as $operation) {
            yield $operation;
        }
    }
}
