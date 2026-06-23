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

namespace Endroid\QrCode\ImageData;

use Endroid\QrCode\Label\LabelInterface;

final readonly class LabelImageData
{
    private function __construct(
        private int $width,
        private int $height,
    ) {
    }

    public static function createForLabel(LabelInterface $label): self {
        if (str_contains($label->getText(), "\n")) {
            throw new \Exception('Label does not support line breaks');
        }

        if (!function_exists('imagettfbbox')) {
            throw new \Exception('Function "imagettfbbox" does not exist: check your FreeType installation');
        }

        $labelBox = imagettfbbox($label->getFont()->getSize(), 0, $label->getFont()->getPath(), $label->getText());

        if (!is_array($labelBox)) {
            throw new \Exception('Unable to generate label image box: check your FreeType installation');
        }

        return new self(
            intval($labelBox[2] - $labelBox[0]),
            intval($labelBox[0] - $labelBox[7])
        );
    }

    public function getWidth(): int {
        return $this->width;
    }

    public function getHeight(): int {
        return $this->height;
    }
}
