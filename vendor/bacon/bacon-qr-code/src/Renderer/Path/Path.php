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
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Path implements IteratorAggregate
{
    /**
     * @var OperationInterface[]
     */
    private array $operations = [];

    /**
     * Moves the drawing operation to a certain position.
     *
     * @param float $x
     * @param float $y
     * @return $this
     */
    public function move(float $x, float $y): self {
        $path = clone $this;
        $path->operations[] = new Move($x, $y);
        return $path;
    }

    /**
     * Draws a line from the current position to another position.
     *
     * @param float $x
     * @param float $y
     * @return $this
     */
    public function line(float $x, float $y): self {
        $path = clone $this;
        $path->operations[] = new Line($x, $y);
        return $path;
    }

    /**
     * Draws an elliptic arc from the current position to another position.
     */
    public function elliptic_arc(
        float $xradius,
        float $yradius,
        float $xaxisrotation,
        bool $largearc,
        bool $sweep,
        float $x,
        float $y
    ): self {
        $path = clone $this;
        $path->operations[] = new EllipticArc($xradius, $yradius, $xaxisrotation, $largearc, $sweep, $x, $y);
        return $path;
    }

    /**
     * Draws a curve from the current position to another position.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $x3
     * @param float $y3
     * @return $this
     */
    public function curve(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): self {
        $path = clone $this;
        $path->operations[] = new Curve($x1, $y1, $x2, $y2, $x3, $y3);
        return $path;
    }

    /**
     * Closes a sub-path.
     *
     * @return $this
     */
    public function close(): self {
        $path = clone $this;
        $path->operations[] = Close::instance();
        return $path;
    }

    /**
     * Appends another path to this one.
     *
     * @param Path $other
     * @return $this
     */
    public function append(self $other): self {
        $path = clone $this;
        $path->operations = array_merge($this->operations, $other->operations);
        return $path;
    }

    /**
     * @param float $x
     * @param float $y
     * @return self
     */
    public function translate(float $x, float $y): self {
        $path = new self();

        foreach ($this->operations as $operation) {
            $path->operations[] = $operation->translate($x, $y);
        }

        return $path;
    }

    /**
     * @param int $degrees
     * @return self
     */
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
