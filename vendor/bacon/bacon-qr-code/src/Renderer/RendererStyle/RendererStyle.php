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

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\SquareModule;

final class RendererStyle
{
    private ModuleInterface $module;

    private EyeInterface|null $eye;

    private Fill $fill;

    public function __construct(
        private int $size,
        private int $margin = 4,
        ?ModuleInterface $module = null,
        ?EyeInterface $eye = null,
        ?Fill $fill = null
    ) {
        $this->module = $module ?: SquareModule::instance();
        $this->eye = $eye ?: new ModuleEye($this->module);
        $this->fill = $fill ?: Fill::default();
    }

    public function withSize(int $size): self {
        $style = clone $this;
        $style->size = $size;
        return $style;
    }

    public function withMargin(int $margin): self {
        $style = clone $this;
        $style->margin = $margin;
        return $style;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getMargin(): int {
        return $this->margin;
    }

    public function getModule(): ModuleInterface {
        return $this->module;
    }

    public function getEye(): EyeInterface {
        return $this->eye;
    }

    public function getFill(): Fill {
        return $this->fill;
    }
}
