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

final class PngResult extends GdResult
{
    public function __construct(
        MatrixInterface $matrix,
        \GdImage $image,
        private readonly int $quality = -1,
        private readonly ?int $numberOfColors = null,
    ) {
        parent::__construct($matrix, $image);
    }

    public function getString(): string {
        ob_start();
        if (null !== $this->numberOfColors) {
            imagetruecolortopalette($this->image, false, $this->numberOfColors);
        }
        imagepng($this->image, quality: $this->quality);

        return strval(ob_get_clean());
    }

    public function getMimeType(): string {
        return 'image/png';
    }
}
