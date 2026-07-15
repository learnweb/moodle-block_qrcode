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
 * Represents the result of writing a QR code using GD.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class GdResult extends AbstractResult
{
    /**
     * Constructs a new GdResult instance with the given matrix and GD image.
     *
     * @param MatrixInterface $matrix
     * @param \GdImage $image
     */
    public function __construct(
        MatrixInterface $matrix,
        /**
         * @var \GdImage
         */
        protected readonly \GdImage $image,
    ) {
        parent::__construct($matrix);
    }

    /**
     * Returns the GD image associated with this result.
     *
     * @return \GdImage
     */
    public function get_image(): \GdImage {
        return $this->image;
    }

    /**
     * Returns the string representation of the QR code.
     *
     * @return string
     * @throws \Exception
     */
    public function get_string(): string {
        throw new \Exception('You can only use this method in a concrete implementation');
    }

    /**
     * Returns the data URI representation of the QR code.
     *
     * @return string
     * @throws \Exception
     */
    public function get_mime_type(): string {
        throw new \Exception('You can only use this method in a concrete implementation');
    }
}
