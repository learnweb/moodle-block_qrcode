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

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\ColorInterface;

/**
 * Defines the fill style for QR code eyes.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EyeFill
{
    /**
     * @var EyeFill|null
     */
    private static ?EyeFill $inherit = null;

    /**
     * Constructor.
     *
     * @param ColorInterface|null $externalcolor
     * @param ColorInterface|null $internalcolor
     */
    public function __construct(
        /**
         * @var ColorInterface|null
         */
        private readonly ?ColorInterface $externalcolor,
        /**
         * @var ColorInterface|null
         */
        private readonly ?ColorInterface $internalcolor
    ) {
    }

    /**
     * Creates a uniform eye fill.
     *
     * @param ColorInterface $color
     * @return self
     */
    public static function uniform(ColorInterface $color): self {
        return new self($color, $color);
    }

    /**
     * Creates an inherited eye fill.
     *
     * @return self
     */
    public static function inherit(): self {
        return self::$inherit ?: self::$inherit = new self(null, null);
    }

    /**
     * Checks whether both colors are inherited.
     *
     * @return bool
     */
    public function inherits_both_colors(): bool {
        return null === $this->externalcolor && null === $this->internalcolor;
    }

    /**
     * Checks whether the external color is inherited.
     *
     * @return bool
     */
    public function inherits_external_color(): bool {
        return null === $this->externalcolor;
    }

    /**
     * Checks whether the internal color is inherited.
     *
     * @return bool
     */
    public function inherits_internal_color(): bool {
        return null === $this->internalcolor;
    }

    /**
     * Returns the external color.
     *
     * @return ColorInterface
     */
    public function get_external_color(): ColorInterface {
        if (null === $this->externalcolor) {
            throw new RuntimeException('External eye color inherits foreground color');
        }

        return $this->externalcolor;
    }

    /**
     * Returns the internal color.
     *
     * @return ColorInterface
     */
    public function get_internal_color(): ColorInterface {
        if (null === $this->internalcolor) {
            throw new RuntimeException('Internal eye color inherits foreground color');
        }

        return $this->internalcolor;
    }
}
