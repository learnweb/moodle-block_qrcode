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
use BaconQrCode\Renderer\Color\Gray;

/**
 * Encapsulates the fill style of a QR code.
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Fill
{
    /**
     * @var Fill|null
     */
    private static ?Fill $default = null;

    /**
     * Constructor.
     *
     * @param ColorInterface $backgroundcolor
     * @param ColorInterface|null $foregroundcolor
     * @param Gradient|null $foregroundgradient
     * @param EyeFill $toplefteyefill
     * @param EyeFill $toprighteyefill
     * @param EyeFill $bottomlefteyefill
     */
    private function __construct(
        /**
         * @var ColorInterface
         */
        private readonly ColorInterface $backgroundcolor,
        /**
         * @var ColorInterface|null
         */
        private readonly ?ColorInterface $foregroundcolor,
        /**
         * @var Gradient|null
         */
        private readonly ?Gradient $foregroundgradient,
        /**
         * @var EyeFill
         */
        private readonly EyeFill $toplefteyefill,
        /**
         * @var EyeFill
         */
        private readonly EyeFill $toprighteyefill,
        /**
         * @var EyeFill
         */
        private readonly EyeFill $bottomlefteyefill
    ) {
    }

    /**
     * Returns the default fill.
     *
     * @return self
     */
    public static function default(): self {
        return self::$default ?: self::$default = self::uniform_color(new Gray(100), new Gray(0));
    }

    /**
     * Creates a fill with a foreground color.
     *
     * @param ColorInterface $backgroundcolor
     * @param ColorInterface $foregroundcolor
     * @param EyeFill $toplefteyefill
     * @param EyeFill $toprighteyefill
     * @param EyeFill $bottomlefteyefill
     * @return self
     */
    public static function with_foreground_color(
        ColorInterface $backgroundcolor,
        ColorInterface $foregroundcolor,
        EyeFill $toplefteyefill,
        EyeFill $toprighteyefill,
        EyeFill $bottomlefteyefill
    ): self {
        return new self(
            $backgroundcolor,
            $foregroundcolor,
            null,
            $toplefteyefill,
            $toprighteyefill,
            $bottomlefteyefill
        );
    }

    /**
     * Creates a fill with a foreground gradient.
     *
     * @param ColorInterface $backgroundcolor
     * @param Gradient $foregroundgradient
     * @param EyeFill $toplefteyefill
     * @param EyeFill $toprighteyefill
     * @param EyeFill $bottomlefteyefill
     * @return self
     */
    public static function with_foreground_gradient(
        ColorInterface $backgroundcolor,
        Gradient $foregroundgradient,
        EyeFill $toplefteyefill,
        EyeFill $toprighteyefill,
        EyeFill $bottomlefteyefill
    ): self {
        return new self(
            $backgroundcolor,
            null,
            $foregroundgradient,
            $toplefteyefill,
            $toprighteyefill,
            $bottomlefteyefill
        );
    }

    /**
     * Creates a uniform color fill.
     *
     * @param ColorInterface $backgroundcolor
     * @param ColorInterface $foregroundcolor
     * @return self
     */
    public static function uniform_color(ColorInterface $backgroundcolor, ColorInterface $foregroundcolor): self {
        return new self(
            $backgroundcolor,
            $foregroundcolor,
            null,
            EyeFill::inherit(),
            EyeFill::inherit(),
            EyeFill::inherit()
        );
    }

    /**
     * Creates a uniform gradient fill.
     *
     * @param ColorInterface $backgroundcolor
     * @param Gradient $foregroundgradient
     * @return self
     */
    public static function uniform_gradient(ColorInterface $backgroundcolor, Gradient $foregroundgradient): self {
        return new self(
            $backgroundcolor,
            null,
            $foregroundgradient,
            EyeFill::inherit(),
            EyeFill::inherit(),
            EyeFill::inherit()
        );
    }

    /**
     * Checks whether the fill uses a gradient.
     *
     * @return bool
     */
    public function has_gradient_fill(): bool {
        return null !== $this->foregroundgradient;
    }

    /**
     * Returns the background color.
     *
     * @return ColorInterface
     */
    public function get_background_color(): ColorInterface {
        return $this->backgroundcolor;
    }

    /**
     * Returns the foreground color.
     *
     * @return ColorInterface
     */
    public function get_foreground_color(): ColorInterface {
        if (null === $this->foregroundcolor) {
            throw new RuntimeException('Fill uses a gradient, thus no foreground color is available');
        }

        return $this->foregroundcolor;
    }

    /**
     * Returns the foreground gradient.
     *
     * @return Gradient
     */
    public function get_foreground_gradient(): Gradient {
        if (null === $this->foregroundgradient) {
            throw new RuntimeException('Fill uses a single color, thus no foreground gradient is available');
        }

        return $this->foregroundgradient;
    }

    /**
     * Returns the top-left eye fill.
     *
     * @return EyeFill
     */
    public function get_top_left_eyefill(): EyeFill {
        return $this->toplefteyefill;
    }

    /**
     * Returns the top-right eye fill.
     *
     * @return EyeFill
     */
    public function get_top_right_eyefill(): EyeFill {
        return $this->toprighteyefill;
    }

    /**
     * Returns the bottom-left eye fill.
     *
     * @return EyeFill
     */
    public function get_bottom_left_eyefill(): EyeFill {
        return $this->bottomlefteyefill;
    }
}
