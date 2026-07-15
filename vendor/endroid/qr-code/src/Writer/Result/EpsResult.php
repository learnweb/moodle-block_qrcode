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
 * Represents the result of writing a QR code in EPS format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EpsResult extends AbstractResult
{
    /**
     * Constructor.
     *
     * @param MatrixInterface $matrix
     * @param array $lines
     */
    public function __construct(
        MatrixInterface $matrix,
        /** @var array<string> $lines */
        private readonly array $lines,
    ) {
        parent::__construct($matrix);
    }

    /**
     * Returns the string representation of the QR code in EPS format.
     *
     * @return string
     */
    public function get_string(): string {
        return implode("\n", $this->lines);
    }

    /**
     * Returns the MIME type for the EPS representation of the QR code.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'image/eps';
    }
}
