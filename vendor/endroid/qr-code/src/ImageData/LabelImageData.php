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

namespace Endroid\QrCode\ImageData;

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Label\LabelInterface;

/**
 * Represents the image data of a label to be used in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class LabelImageData
{
    /**
     * Creates a new instance of LabelImageData with the provided width and height.
     *
     * @param int $width
     * @param int $height
     */
    private function __construct(
        /**
         * @var int
         */
        private int $width,
        /**
         * @var int
         */
        private int $height,
    ) {
    }

    /**
     * Creates a new instance of LabelImageData for the given label.
     *
     * @param LabelInterface $label
     * @return self
     * @throws \Exception
     */
    public static function create_for_label(LabelInterface $label): self {
        if (str_contains($label->get_text(), "\n")) {
            throw new \Exception('Label does not support line breaks');
        }

        if (!function_exists('imagettfbbox')) {
            throw new \Exception('Function "imagettfbbox" does not exist: check your FreeType installation');
        }

        $labelbox = imagettfbbox($label->get_font()->get_size(), 0, $label->get_font()->get_path(), $label->get_text());

        if (!is_array($labelbox)) {
            throw new \Exception('Unable to generate label image box: check your FreeType installation');
        }

        return new self(
            intval($labelbox[2] - $labelbox[0]),
            intval($labelbox[0] - $labelbox[7])
        );
    }

    /**
     * Returns the width of the label image data.
     *
     * @return int
     */
    public function get_width(): int {
        return $this->width;
    }

    /**
     * Returns the height of the label image data.
     *
     * @return int
     */
    public function get_height(): int {
        return $this->height;
    }
}
