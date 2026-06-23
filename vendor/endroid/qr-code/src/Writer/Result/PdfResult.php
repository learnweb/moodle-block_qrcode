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

final class PdfResult extends AbstractResult
{
    public function __construct(
        MatrixInterface $matrix,
        private readonly \FPDF $fpdf,
    ) {
        parent::__construct($matrix);
    }

    public function getPdf(): \FPDF {
        return $this->fpdf;
    }

    public function getString(): string {
        return $this->fpdf->Output('S');
    }

    public function getMimeType(): string {
        return 'application/pdf';
    }
}
