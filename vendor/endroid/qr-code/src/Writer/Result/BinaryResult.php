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
 * Represents the result of writing a QR code in binary format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class BinaryResult extends AbstractResult
{
    /**
     * Creates a new instance of BinaryResult.
     *
     * @param MatrixInterface $matrix
     */
    public function __construct(MatrixInterface $matrix) {
        parent::__construct($matrix);
    }

    /**
     * Returns the string representation of the QR code in binary format.
     *
     * @return string
     */
    public function get_string(): string {
        $matrix = $this->get_matrix();

        $binarystring = '';
        for ($rowindex = 0; $rowindex < $matrix->get_block_count(); ++$rowindex) {
            for ($columnindex = 0; $columnindex < $matrix->get_block_count(); ++$columnindex) {
                $binarystring .= $matrix->get_block_value($rowindex, $columnindex);
            }
            $binarystring .= "\n";
        }

        return $binarystring;
    }

    /**
     * Returns the MIME type for the binary representation of the QR code.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'text/plain';
    }
}
