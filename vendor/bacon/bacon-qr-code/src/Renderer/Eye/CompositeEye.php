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
 * Combines the style of two different eyes.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class CompositeEye implements EyeInterface
{
    /**
     * Constructor.
     *
     * @param EyeInterface $externaleye
     * @param EyeInterface $internaleye
     */
    public function __construct(
        /**
         * @var EyeInterface
         */
        private readonly EyeInterface $externaleye,
        /**
         * @var EyeInterface
         */
        private readonly EyeInterface $internaleye
    ) {
    }

    /**
     * Returns the external path.
     *
     * @return Path
     */
    public function get_external_path(): Path {
        return $this->externaleye->get_external_path();
    }

    /**
     * Returns the internal path.
     *
     * @return Path
     */
    public function get_internal_path(): Path {
        return $this->internaleye->get_internal_path();
    }
}
