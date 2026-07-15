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

namespace Endroid\QrCode\Writer\Result;

use Endroid\QrCode\Matrix\MatrixInterface;

/**
 * Represents the result of writing a QR code in WebP format.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class WebPResult extends GdResult
{
    /**
     * Constructor.
     *
     * @param MatrixInterface $matrix
     * @param \GdImage $image
     * @param int $quality
     */
    public function __construct(
        MatrixInterface $matrix,
        \GdImage $image,
        /**
         * @var int
         */
        private readonly int $quality = -1,
    ) {
        parent::__construct($matrix, $image);
    }

    /**
     * Returns the string representation of the QR code in WebP format.
     *
     * @return string
     * @throws \Exception
     */
    public function get_string(): string {
        if (!function_exists('imagewebp')) {
            throw new \Exception('WebP support is not available in your GD installation');
        }

        ob_start();
        imagewebp($this->image, quality: $this->quality);

        return strval(ob_get_clean());
    }

    /**
     * Returns the MIME type for WebP images.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'image/webp';
    }
}
