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
 * Renders the outer eye as solid with a curved corner and inner eye as a circle.
 */
final class PointyEye implements EyeInterface
{
    /**
     * @var self|null
     */
    private static $instance;

    private function __construct() {
    }

    public static function instance(): self {
        return self::$instance ?: self::$instance = new self();
    }

    public function getExternalPath(): Path {
        return (new Path())
            ->move(-3.5, 3.5)
            ->line(-3.5, 0)
            ->elliptic_arc(3.5, 3.5, 0, false, true, 0, -3.5)
            ->line(3.5, -3.5)
            ->line(3.5, 3.5)
            ->close()
            ->move(2.5, 0)
            ->elliptic_arc(2.5, 2.5, 0, false, true, 0, 2.5)
            ->elliptic_arc(2.5, 2.5, 0, false, true, -2.5, 0)
            ->elliptic_arc(2.5, 2.5, 0, false, true, 0, -2.5)
            ->elliptic_arc(2.5, 2.5, 0, false, true, 2.5, 0)
            ->close();
    }

    public function getInternalPath(): Path {
        return (new Path())
            ->move(1.5, 0)
            ->elliptic_arc(1.5, 1.5, 0., false, true, 0., 1.5)
            ->elliptic_arc(1.5, 1.5, 0., false, true, -1.5, 0.)
            ->elliptic_arc(1.5, 1.5, 0., false, true, 0., -1.5)
            ->elliptic_arc(1.5, 1.5, 0., false, true, 1.5, 0.)
            ->close();
    }
}
