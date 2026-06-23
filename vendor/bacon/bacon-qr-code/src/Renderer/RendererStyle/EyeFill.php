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

final class EyeFill
{
    private static ?EyeFill $inherit = null;

    public function __construct(
        private readonly ?ColorInterface $externalColor,
        private readonly ?ColorInterface $internalColor
    ) {
    }

    public static function uniform(ColorInterface $color): self {
        return new self($color, $color);
    }

    public static function inherit(): self {
        return self::$inherit ?: self::$inherit = new self(null, null);
    }

    public function inheritsBothColors(): bool {
        return null === $this->externalColor && null === $this->internalColor;
    }

    public function inheritsExternalColor(): bool {
        return null === $this->externalColor;
    }

    public function inheritsInternalColor(): bool {
        return null === $this->internalColor;
    }

    public function getExternalColor(): ColorInterface {
        if (null === $this->externalColor) {
            throw new RuntimeException('External eye color inherits foreground color');
        }

        return $this->externalColor;
    }

    public function getInternalColor(): ColorInterface {
        if (null === $this->internalColor) {
            throw new RuntimeException('Internal eye color inherits foreground color');
        }

        return $this->internalColor;
    }
}
