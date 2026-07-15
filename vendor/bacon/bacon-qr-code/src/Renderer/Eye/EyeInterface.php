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

use BaconQrCode\Renderer\Path\Path;

/**
 * Interface for describing the look of an eye.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface EyeInterface
{
    /**
     *  Returns the path of the external eye element.
     *  The path origin point (0, 0) must be anchored at the middle of the path.
     *
     * @return Path
     */
    public function get_external_path(): Path;

    /**
     *  Returns the path of the internal eye element.
     *  The path origin point (0, 0) must be anchored at the middle of the path.
     *
     * @return Path
     */
    public function get_internal_path(): Path;
}
