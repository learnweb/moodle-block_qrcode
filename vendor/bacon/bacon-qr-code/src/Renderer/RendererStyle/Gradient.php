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

namespace BaconQrCode\Renderer\RendererStyle;

use BaconQrCode\Renderer\Color\ColorInterface;

/**
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Gradient
{
    /**
     * Construct.
     *
     * @param ColorInterface $startcolor
     * @param ColorInterface $endcolor
     * @param GradientType $type
     */
    public function __construct(
        /**
         * @var ColorInterface
         */
        private readonly ColorInterface $startcolor,
        /**
         * @var ColorInterface
         */
        private readonly ColorInterface $endcolor,
        /**
         * @var GradientType
         */
        private readonly GradientType $type
    ) {
    }

    /**
     * Returns start color.
     *
     * @return ColorInterface
     */
    public function get_start_color(): ColorInterface {
        return $this->startcolor;
    }

    /**
     * Returns end color.
     *
     * @return ColorInterface
     */
    public function get_end_color(): ColorInterface {
        return $this->endcolor;
    }

    /**
     * Returns gradient type.
     *
     * @return GradientType
     */
    public function get_type(): GradientType {
        return $this->type;
    }
}
