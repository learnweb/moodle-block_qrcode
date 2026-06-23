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

final class Gradient
{
    public function __construct(
        private readonly ColorInterface $startColor,
        private readonly ColorInterface $endColor,
        private readonly GradientType $type
    ) {
    }

    public function getStartColor(): ColorInterface {
        return $this->startColor;
    }

    public function getEndColor(): ColorInterface {
        return $this->endColor;
    }

    public function getType(): GradientType {
        return $this->type;
    }
}
