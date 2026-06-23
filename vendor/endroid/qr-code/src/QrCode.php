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

namespace Endroid\QrCode;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Encoding\EncodingInterface;

final readonly class QrCode implements QrCodeInterface
{
    public function __construct(
        private string $data,
        private EncodingInterface $encoding = new Encoding('UTF-8'),
        private ErrorCorrectionLevel $errorCorrectionLevel = ErrorCorrectionLevel::Low,
        private int $size = 300,
        private int $margin = 10,
        private RoundBlockSizeMode $roundBlockSizeMode = RoundBlockSizeMode::Margin,
        private ColorInterface $foregroundColor = new Color(0, 0, 0),
        private ColorInterface $backgroundColor = new Color(255, 255, 255),
    ) {
    }

    public function getData(): string {
        return $this->data;
    }

    public function getEncoding(): EncodingInterface {
        return $this->encoding;
    }

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel {
        return $this->errorCorrectionLevel;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getMargin(): int {
        return $this->margin;
    }

    public function getRoundBlockSizeMode(): RoundBlockSizeMode {
        return $this->roundBlockSizeMode;
    }

    public function getForegroundColor(): ColorInterface {
        return $this->foregroundColor;
    }

    public function getBackgroundColor(): ColorInterface {
        return $this->backgroundColor;
    }
}
