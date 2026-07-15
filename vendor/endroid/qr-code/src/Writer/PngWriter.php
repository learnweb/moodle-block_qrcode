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

namespace Endroid\QrCode\Writer;

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\GdResult;
use Endroid\QrCode\Writer\Result\PngResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Writer for generating QR codes in PNG format.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class PngWriter extends AbstractGdWriter
{
    /**
     * Compression level option.
     */
    public const WRITER_OPTION_COMPRESSION_LEVEL = 'compression_level';
    /**
     * Number of colors option.
     */
    public const WRITER_OPTION_NUMBER_OF_COLORS = 'number_of_colors';

    /**
     * Writes a QR code to PNG format, optionally including a logo and label.
     *
     * @param QrCodeInterface $qrcode
     * @param LogoInterface|null $logo
     * @param LabelInterface|null $label
     * @param array $options
     * @return ResultInterface
     * @throws \Exception
     */
    public function write(
        QrCodeInterface $qrcode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        array $options = []
    ): ResultInterface {
        if (!isset($options[self::WRITER_OPTION_COMPRESSION_LEVEL])) {
            $options[self::WRITER_OPTION_COMPRESSION_LEVEL] = -1;
        }

        if (!array_key_exists(self::WRITER_OPTION_NUMBER_OF_COLORS, $options)) {
            $options[self::WRITER_OPTION_NUMBER_OF_COLORS] = match (true) {
                $qrcode->get_background_color()->get_alpha() > 0 || $qrcode->get_foreground_color()->get_alpha() > 0 => null,
                $logo instanceof LogoInterface => null,
                default => 16,
            };
        }

        $gdresult = parent::write($qrcode, $logo, $label, $options);

        return new PngResult(
            $gdresult->get_matrix(),
            $gdresult->get_image(),
            $options[self::WRITER_OPTION_COMPRESSION_LEVEL],
            $options[self::WRITER_OPTION_NUMBER_OF_COLORS]
        );
    }
}
