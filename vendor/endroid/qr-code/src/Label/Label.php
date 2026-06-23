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

namespace Endroid\QrCode\Label;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Font\FontInterface;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Label\Margin\MarginInterface;

final readonly class Label implements LabelInterface
{
    public function __construct(
        private string $text,
        private FontInterface $font = new Font(__DIR__ . '/../../assets/open_sans.ttf', 16),
        private LabelAlignment $alignment = LabelAlignment::Center,
        private MarginInterface $margin = new Margin(0, 10, 10, 10),
        private ColorInterface $textColor = new Color(0, 0, 0),
    ) {
    }

    public function getText(): string {
        return $this->text;
    }

    public function getFont(): FontInterface {
        return $this->font;
    }

    public function getAlignment(): LabelAlignment {
        return $this->alignment;
    }

    public function getMargin(): MarginInterface {
        return $this->margin;
    }

    public function getTextColor(): ColorInterface {
        return $this->textColor;
    }
}
