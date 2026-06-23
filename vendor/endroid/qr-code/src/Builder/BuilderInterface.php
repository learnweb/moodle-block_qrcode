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

namespace Endroid\QrCode\Builder;

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Encoding\EncodingInterface;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\FontInterface;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\MarginInterface;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\WriterInterface;

interface BuilderInterface
{
    /** @param array<mixed>|null $writerOptions */
    public function build(
        ?WriterInterface $writer = null,
        ?array $writerOptions = null,
        ?bool $validateResult = null,
        // QrCode options
        ?string $data = null,
        ?EncodingInterface $encoding = null,
        ?ErrorCorrectionLevel $errorCorrectionLevel = null,
        ?int $size = null,
        ?int $margin = null,
        ?RoundBlockSizeMode $roundBlockSizeMode = null,
        ?ColorInterface $foregroundColor = null,
        ?ColorInterface $backgroundColor = null,
        // Label options
        ?string $labelText = null,
        ?FontInterface $labelFont = null,
        ?LabelAlignment $labelAlignment = null,
        ?MarginInterface $labelMargin = null,
        ?ColorInterface $labelTextColor = null,
        // Logo options
        ?string $logoPath = null,
        ?int $logoResizeToWidth = null,
        ?int $logoResizeToHeight = null,
        ?bool $logoPunchoutBackground = null,
    ): ResultInterface;
}
