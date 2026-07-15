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

namespace Endroid\QrCode;

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Encoding\EncodingInterface;

/**
 * Interface for a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface QrCodeInterface
{
    /**
     * Returns the data encoded in the QR code.
     *
     * @return string
     */
    public function get_data(): string;

    /**
     * Returns the encoding used for the QR code.
     *
     * @return EncodingInterface
     */
    public function get_encoding(): EncodingInterface;

    /**
     * Returns the error correction level of the QR code.
     *
     * @return ErrorCorrectionLevel
     */
    public function get_error_correction_level(): ErrorCorrectionLevel;

    /**
     * Returns the size of the QR code in pixels.
     *
     * @return int
     */
    public function get_size(): int;

    /**
     * Returns the margin of the QR code in pixels.
     *
     * @return int
     */
    public function get_margin(): int;

    /**
     * Returns the round block size mode of the QR code.
     *
     * @return RoundBlockSizeMode
     */
    public function get_roundblock_size_mode(): RoundBlockSizeMode;

    /**
     * Returns the foreground color of the QR code.
     *
     * @return ColorInterface
     */
    public function get_foreground_color(): ColorInterface;

    /**
     * Returns the background color of the QR code.
     *
     * @return ColorInterface
     */
    public function get_background_color(): ColorInterface;
}
