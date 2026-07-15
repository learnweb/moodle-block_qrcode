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

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Label\Font\FontInterface;
use Endroid\QrCode\Label\Margin\MarginInterface;

/**
 * Interface for a label in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface LabelInterface
{
    /**
     * Returns the text of the label.
     *
     * @return string
     */
    public function get_text(): string;

    /**
     * Returns the font of the label.
     *
     * @return FontInterface
     */
    public function get_font(): FontInterface;

    /**
     * Returns the alignment of the label.
     *
     * @return LabelAlignment
     */
    public function get_alignment(): LabelAlignment;

    /**
     * Returns the margin of the label.
     *
     * @return MarginInterface
     */
    public function get_margin(): MarginInterface;

    /**
     * Returns the text color of the label.
     *
     * @return ColorInterface
     */
    public function get_text_color(): ColorInterface;
}
