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

/**
 * Defines the renderer style for a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class RendererStyle
{
    /**
     * @var ModuleInterface|SquareModule
     */
    private ModuleInterface $module;
    /**
     * @var EyeInterface|ModuleEye|null
     */
    private EyeInterface|null $eye;
    /**
     * @var Fill
     */
    private Fill $fill;

    /**
     * Constructor.
     *
     * @param int $size
     * @param int $margin
     * @param ModuleInterface|null $module
     * @param EyeInterface|null $eye
     * @param Fill|null $fill
     */
    public function __construct(
        /**
         * @var int
         */
        private int $size,
        /**
         * @var int
         */
        private int $margin = 4,
        ?ModuleInterface $module = null,
        ?EyeInterface $eye = null,
        ?Fill $fill = null
    ) {
        $this->module = $module ?: SquareModule::instance();
        $this->eye = $eye ?: new ModuleEye($this->module);
        $this->fill = $fill ?: Fill::default();
    }

    /**
     * Returns a copy with the given size.
     *
     * @param int $size
     * @return $this
     */
    public function with_size(int $size): self {
        $style = clone $this;
        $style->size = $size;
        return $style;
    }

    /**
     * Returns a copy with the given margin.
     *
     * @param int $margin
     * @return $this
     */
    public function with_margin(int $margin): self {
        $style = clone $this;
        $style->margin = $margin;
        return $style;
    }

    /**
     * Returns the size.
     *
     * @return int
     */
    public function get_size(): int {
        return $this->size;
    }

    /**
     * Returns the margin.
     *
     * @return int
     */
    public function get_margin(): int {
        return $this->margin;
    }

    /**
     * Returns the module renderer.
     *
     * @return ModuleInterface
     */
    public function get_module(): ModuleInterface {
        return $this->module;
    }

    /**
     * Returns the eye renderer.
     *
     * @return EyeInterface
     */
    public function get_eye(): EyeInterface {
        return $this->eye;
    }

    /**
     * Returns the fill style.
     *
     * @return Fill
     */
    public function get_fill(): Fill {
        return $this->fill;
    }
}
