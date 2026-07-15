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

namespace BaconQrCode\Renderer\Eye;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Path\Path;

/**
 * Renders an eye based on a module renderer.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ModuleEye implements EyeInterface
{
    /**
     * Constructor.
     *
     * @param ModuleInterface $module
     */
    public function __construct(
        /**
         * @var ModuleInterface
         */
        private readonly ModuleInterface $module
    ) {
    }

    /**
     * Returns the external path.
     *
     * @return Path
     */
    public function get_external_path(): Path {
        $matrix = new ByteMatrix(7, 7);

        for ($x = 0; $x < 7; ++$x) {
            $matrix->set($x, 0, 1);
            $matrix->set($x, 6, 1);
        }

        for ($y = 1; $y < 6; ++$y) {
            $matrix->set(0, $y, 1);
            $matrix->set(6, $y, 1);
        }

        return $this->module->create_path($matrix)->translate(-3.5, -3.5);
    }

    /**
     * Returns the internal path.
     *
     * @return Path
     */
    public function get_internal_path(): Path {
        $matrix = new ByteMatrix(3, 3);

        for ($x = 0; $x < 3; ++$x) {
            for ($y = 0; $y < 3; ++$y) {
                $matrix->set($x, $y, 1);
            }
        }

        return $this->module->create_path($matrix)->translate(-1.5, -1.5);
    }
}
