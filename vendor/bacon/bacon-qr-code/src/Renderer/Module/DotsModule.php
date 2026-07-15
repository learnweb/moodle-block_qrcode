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
use BaconQrCode\Renderer\Path\Path;

/**
 * Renders individual modules as dots.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class DotsModule implements ModuleInterface
{
    /**
     * Large dot size.
     */
    public const LARGE = 1;
    /**
     * Medium dot size.
     */
    public const MEDIUM = .8;
    /**
     * Small dot size.
     */
    public const SMALL = .6;

    /**
     * Constructor.
     *
     * @param float $size
     */
    public function __construct(
        /**
         * @var float
         */
        private readonly float $size
    ) {
        if ($size <= 0 || $size > 1) {
            throw new InvalidArgumentException('Size must between 0 (exclusive) and 1 (inclusive)');
        }
    }

    /**
     * Creates a path from the byte matrix.
     *
     * @param ByteMatrix $matrix
     * @return Path
     */
    public function create_path(ByteMatrix $matrix): Path {
        $width = $matrix->get_width();
        $height = $matrix->get_height();
        $path = new Path();
        $halfsize = $this->size / 2;
        $margin = (1 - $this->size) / 2;

        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                if (! $matrix->get($x, $y)) {
                    continue;
                }

                $pathx = $x + $margin;
                $pathy = $y + $margin;

                $path = $path
                    ->move($pathx + $this->size, $pathy + $halfsize)
                    ->elliptic_arc($halfsize, $halfsize, 0, false, true, $pathx + $halfsize, $pathy + $this->size)
                    ->elliptic_arc($halfsize, $halfsize, 0, false, true, $pathx, $pathy + $halfsize)
                    ->elliptic_arc($halfsize, $halfsize, 0, false, true, $pathx + $halfsize, $pathy)
                    ->elliptic_arc($halfsize, $halfsize, 0, false, true, $pathx + $this->size, $pathy + $halfsize)
                    ->close();
            }
        }

        return $path;
    }
}
