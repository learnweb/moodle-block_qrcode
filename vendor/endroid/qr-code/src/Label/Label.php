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

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Font\FontInterface;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Label\Margin\MarginInterface;

/**
 * Represents a label in a QR code, including its text, font, alignment, margin, and text color.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class Label implements LabelInterface
{
    /**
     * Constructor.
     *
     * @param string $text
     * @param FontInterface $font
     * @param LabelAlignment $alignment
     * @param MarginInterface $margin
     * @param ColorInterface $textcolor
     * @throws \Exception
     */
    public function __construct(
        /**
         * @var string
         */
        private string $text,
        /**
         * @var FontInterface|Font
         */
        private FontInterface $font = new Font(__DIR__ . '/../../assets/open_sans.ttf', 16),
        /**
         * @var LabelAlignment
         */
        private LabelAlignment $alignment = LabelAlignment::Center,
        /**
         * @var MarginInterface|Margin
         */
        private MarginInterface $margin = new Margin(0, 10, 10, 10),
        /**
         * @var ColorInterface|Color
         */
        private ColorInterface $textcolor = new Color(0, 0, 0),
    ) {
    }

    /**
     * Returns the text of the label.
     *
     * @return string
     */
    public function get_text(): string {
        return $this->text;
    }

    /**
     * Returns the font of the label.
     *
     * @return FontInterface
     */
    public function get_font(): FontInterface {
        return $this->font;
    }

    /**
     * Returns the alignment of the label.
     *
     * @return LabelAlignment
     */
    public function get_alignment(): LabelAlignment {
        return $this->alignment;
    }

    /**
     * Returns the margin of the label.
     *
     * @return MarginInterface
     */
    public function get_margin(): MarginInterface {
        return $this->margin;
    }

    /**
     * Returns the text color of the label.
     *
     * @return ColorInterface
     */
    public function get_text_color(): ColorInterface {
        return $this->textcolor;
    }
}
