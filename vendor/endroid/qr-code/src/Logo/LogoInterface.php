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

namespace Endroid\QrCode\Logo;

/**
 * Interface for a logo to be used in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface LogoInterface
{
    /**
     * Returns the path to the logo image.
     *
     * @return string
     */
    public function get_path(): string;

    /**
     * Returns the width to which the logo should be resized, or null if no resizing is needed.
     *
     * @return int|null
     */
    public function get_resize_to_width(): ?int;

    /**
     * Returns the height to which the logo should be resized, or null if no resizing is needed.
     *
     * @return int|null
     */
    public function get_resize_to_height(): ?int;

    /**
     * Returns whether the background of the logo should be punched out (made transparent) when placed in the QR code.
     *
     * @return bool
     */
    public function get_punchout_background(): bool;
}
