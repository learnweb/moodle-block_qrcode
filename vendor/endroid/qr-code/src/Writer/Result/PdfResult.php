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
 * Represents the result of writing a QR code in PDF format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class PdfResult extends AbstractResult
{
    /**
     * Constructor.
     *
     * @param MatrixInterface $matrix
     * @param \FPDF $fpdf
     */
    public function __construct(
        MatrixInterface $matrix,
        /**
         * @var \FPDF
         */
        private readonly \FPDF $fpdf,
    ) {
        parent::__construct($matrix);
    }

    /**
     * Returns the FPDF instance representing the PDF.
     *
     * @return \FPDF
     */
    public function get_pdf(): \FPDF {
        return $this->fpdf;
    }

    /**
     * Returns the string representation of the QR code in PDF format.
     *
     * @return string
     */
    public function get_string(): string {
        return $this->fpdf->Output('S');
    }

    /**
     * Returns the MIME type for the PDF representation of the QR code.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'application/pdf';
    }
}
