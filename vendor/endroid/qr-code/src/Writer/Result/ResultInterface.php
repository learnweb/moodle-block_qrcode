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
 * Interface for a result of writing a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface ResultInterface
{
    /**
     * Returns the matrix of blocks for the QR code.
     *
     * @return MatrixInterface
     */
    public function get_matrix(): MatrixInterface;

    /**
     * Returns the string representation of the QR code.
     *
     * @return string
     */
    public function get_string(): string;

    /**
     * Returns the data URI representation of the QR code.
     *
     * @return string
     */
    public function get_data_uri(): string;

    /**
     * Saves the QR code to a file at the specified path.
     *
     * @param string $path
     * @return void
     */
    public function save_to_file(string $path): void;

    /**
     * Returns the MIME type of the QR code.
     *
     * @return string
     */
    public function get_mime_type(): string;
}
