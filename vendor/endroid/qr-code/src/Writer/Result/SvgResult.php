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

final class SvgResult extends AbstractResult
{
    public function __construct(
        MatrixInterface $matrix,
        private readonly \SimpleXMLElement $xml,
        private readonly bool $excludeXmlDeclaration = false,
    ) {
        parent::__construct($matrix);
    }

    public function getXml(): \SimpleXMLElement {
        return $this->xml;
    }

    public function getString(): string {
        $string = $this->xml->asXML();

        if (!is_string($string)) {
            throw new \Exception('Could not save SVG XML to string');
        }

        if ($this->excludeXmlDeclaration) {
            $string = str_replace("<?xml version=\"1.0\"?>\n", '', $string);
        }

        return $string;
    }

    public function getMimeType(): string {
        return 'image/svg+xml';
    }
}
