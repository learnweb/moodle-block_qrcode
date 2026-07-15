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

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Encoding\EncodingInterface;

/**
 * Represents a QR code with its associated properties and settings.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class QrCode implements QrCodeInterface
{
    /**
     * Construct.
     *
     * @param string $data
     * @param EncodingInterface $encoding
     * @param ErrorCorrectionLevel $errorcorrectionlevel
     * @param int $size
     * @param int $margin
     * @param RoundBlockSizeMode $roundblocksizemode
     * @param ColorInterface $foregroundcolor
     * @param ColorInterface $backgroundcolor
     */
    public function __construct(
        /**
         * @var string
         */
        private string $data,
        /**
         * @var EncodingInterface|Encoding
         */
        private EncodingInterface $encoding = new Encoding('UTF-8'),
        /**
         * @var ErrorCorrectionLevel
         */
        private ErrorCorrectionLevel $errorcorrectionlevel = ErrorCorrectionLevel::Low,
        /**
         * @var int
         */
        private int $size = 300,
        /**
         * @var int
         */
        private int $margin = 10,
        /**
         * @var RoundBlockSizeMode
         */
        private RoundBlockSizeMode $roundblocksizemode = RoundBlockSizeMode::Margin,
        /**
         * @var ColorInterface|Color
         */
        private ColorInterface $foregroundcolor = new Color(0, 0, 0),
        /**
         * @var ColorInterface|Color
         */
        private ColorInterface $backgroundcolor = new Color(255, 255, 255),
    ) {
    }

    /**
     * Returns the data encoded in the QR code.
     *
     * @return string
     */
    public function get_data(): string {
        return $this->data;
    }

    /**
     * Returns the encoding used for the QR code.
     *
     * @return EncodingInterface
     */
    public function get_encoding(): EncodingInterface {
        return $this->encoding;
    }

    /**
     * Returns the error correction level of the QR code.
     *
     * @return ErrorCorrectionLevel
     */
    public function get_error_correction_level(): ErrorCorrectionLevel {
        return $this->errorcorrectionlevel;
    }

    /**
     * Returns the size of the QR code in pixels.
     *
     * @return int
     */
    public function get_size(): int {
        return $this->size;
    }

    /**
     * Returns the margin of the QR code in pixels.
     *
     * @return int
     */
    public function get_margin(): int {
        return $this->margin;
    }

    /**
     * Returns the round block size mode of the QR code.
     *
     * @return RoundBlockSizeMode
     */
    public function get_roundblock_size_mode(): RoundBlockSizeMode {
        return $this->roundblocksizemode;
    }

    /**
     * Returns the foreground color of the QR code.
     *
     * @return ColorInterface
     */
    public function get_foreground_color(): ColorInterface {
        return $this->foregroundcolor;
    }

    /**
     * Returns the background color of the QR code.
     *
     * @return ColorInterface
     */
    public function get_background_color(): ColorInterface {
        return $this->backgroundcolor;
    }
}
