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
 * Represents the result of writing a QR code in SVG format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class SvgResult extends AbstractResult
{
    /**
     * Creates a new instance of SvgResult.
     *
     * @param MatrixInterface $matrix
     * @param \SimpleXMLElement $xml
     * @param bool $excludexmldeclaration
     */
    public function __construct(
        MatrixInterface $matrix,
        /**
         * @var \SimpleXMLElement
         */
        private readonly \SimpleXMLElement $xml,
        /**
         * @var bool
         */
        private readonly bool $excludexmldeclaration = false,
    ) {
        parent::__construct($matrix);
    }

    /**
     * Returns the SimpleXMLElement representing the SVG.
     *
     * @return \SimpleXMLElement
     */
    public function getxml(): \SimpleXMLElement {
        return $this->xml;
    }

    /**
     * Returns the string representation of the SVG.
     *
     * @return string
     * @throws \Exception
     */
    public function get_string(): string {
        $string = $this->xml->asXML();

        if (!is_string($string)) {
            throw new \Exception('Could not save SVG XML to string');
        }

        if ($this->excludexmldeclaration) {
            $string = str_replace("<?xml version=\"1.0\"?>\n", '', $string);
        }

        return $string;
    }

    /**
     * Returns the MIME type for SVG.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'image/svg+xml';
    }
}
