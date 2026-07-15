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

namespace BaconQrCode\Renderer\Module;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Renderer\Module\EdgeIterator\EdgeIterator;
use BaconQrCode\Renderer\Path\Path;

/**
 * Rounds the corners of module groups.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class RoundnessModule implements ModuleInterface
{
    /**
     * Strong roundness.
     */
    public const STRONG = 1;
    /**
     * Medium roundness.
     */
    public const MEDIUM = .5;
    /**
     * Soft roundness.
     */
    public const SOFT = .25;

    /**
     * Constructor.
     *
     * @param float $intensity
     */
    public function __construct(
        /**
         * @var float
         */
        private float $intensity
    ) {
        if ($intensity <= 0 || $intensity > 1) {
            throw new InvalidArgumentException('Intensity must between 0 (exclusive) and 1 (inclusive)');
        }

        $this->intensity = $intensity / 2;
    }

    /**
     * Creates a path from the byte matrix.
     *
     * @param ByteMatrix $matrix
     * @return Path
     */
    public function create_path(ByteMatrix $matrix): Path {
        $path = new Path();

        foreach (new EdgeIterator($matrix) as $edge) {
            $points = $edge->get_simplified_points();
            $length = count($points);

            $currentpoint = $points[0];
            $nextpoint = $points[1];
            $horizontal = ($currentpoint[1] === $nextpoint[1]);

            if ($horizontal) {
                $right = $nextpoint[0] > $currentpoint[0];
                $path = $path->move(
                    $currentpoint[0] + ($right ? $this->intensity : -$this->intensity),
                    $currentpoint[1]
                );
            } else {
                $up = $nextpoint[0] < $currentpoint[0];
                $path = $path->move(
                    $currentpoint[0],
                    $currentpoint[1] + ($up ? -$this->intensity : $this->intensity)
                );
            }

            for ($i = 1; $i <= $length; ++$i) {
                if ($i === $length) {
                    $previouspoint = $points[$length - 1];
                    $currentpoint = $points[0];
                    $nextpoint = $points[1];
                } else {
                    $previouspoint = $points[(0 === $i ? $length : $i) - 1];
                    $currentpoint = $points[$i];
                    $nextpoint = $points[($length - 1 === $i ? -1 : $i) + 1];
                }

                $horizontal = ($previouspoint[1] === $currentpoint[1]);

                if ($horizontal) {
                    $right = $previouspoint[0] < $currentpoint[0];
                    $up = $nextpoint[1] < $currentpoint[1];
                    $sweep = ($up xor $right);

                    if (
                        $this->intensity < 0.5
                        || ($right && $previouspoint[0] !== $currentpoint[0] - 1)
                        || (! $right && $previouspoint[0] - 1 !== $currentpoint[0])
                    ) {
                        $path = $path->line(
                            $currentpoint[0] + ($right ? -$this->intensity : $this->intensity),
                            $currentpoint[1]
                        );
                    }

                    $path = $path->elliptic_arc(
                        $this->intensity,
                        $this->intensity,
                        0,
                        false,
                        $sweep,
                        $currentpoint[0],
                        $currentpoint[1] + ($up ? -$this->intensity : $this->intensity)
                    );
                } else {
                    $up = $previouspoint[1] > $currentpoint[1];
                    $right = $nextpoint[0] > $currentpoint[0];
                    $sweep = ! ($up xor $right);

                    if (
                        $this->intensity < 0.5
                        || ($up && $previouspoint[1] !== $currentpoint[1] + 1)
                        || (! $up && $previouspoint[0] + 1 !== $currentpoint[0])
                    ) {
                        $path = $path->line(
                            $currentpoint[0],
                            $currentpoint[1] + ($up ? $this->intensity : -$this->intensity)
                        );
                    }

                    $path = $path->elliptic_arc(
                        $this->intensity,
                        $this->intensity,
                        0,
                        false,
                        $sweep,
                        $currentpoint[0] + ($right ? $this->intensity : -$this->intensity),
                        $currentpoint[1]
                    );
                }
            }

            $path = $path->close();
        }

        return $path;
    }
}
