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
use BaconQrCode\Renderer\Module\EdgeIterator\EdgeIterator;
use BaconQrCode\Renderer\Path\Path;

/**
 * Groups modules together to a single path.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class SquareModule implements ModuleInterface
{
    /**
     * @var SquareModule|null
     */
    private static ?SquareModule $instance = null;

    /**
     * Constructor.
     */
    private function __construct() {
    }

    /**
     * Returns the square module instance.
     *
     * @return self
     */
    public static function instance(): self {
        return self::$instance ?: self::$instance = new self();
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
            $path = $path->move($points[0][0], $points[0][1]);

            for ($i = 1; $i < $length; ++$i) {
                $path = $path->line($points[$i][0], $points[$i][1]);
            }

            $path = $path->close();
        }

        return $path;
    }
}
