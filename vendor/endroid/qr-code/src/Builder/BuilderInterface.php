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

/**
 * Interface for a builder that creates QR codes with optional labels and logos.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface BuilderInterface
{
    /**
     * Builds a QR code with the specified options and returns the result.
     *
     * @param WriterInterface|null $writer
     * @param array|null $writeroptions
     * @param bool|null $validateresult
     * @param string|null $data
     * @param EncodingInterface|null $encoding
     * @param ErrorCorrectionLevel|null $errorcorrectionlevel
     * @param int|null $size
     * @param int|null $margin
     * @param RoundBlockSizeMode|null $roundblocksizemode
     * @param ColorInterface|null $foregroundcolor
     * @param ColorInterface|null $backgroundcolor
     * @param string|null $labeltext
     * @param FontInterface|null $labelfont
     * @param LabelAlignment|null $labelalignment
     * @param MarginInterface|null $labelmargin
     * @param ColorInterface|null $labeltextcolor
     * @param string|null $logopath
     * @param int|null $logoresizetowidth
     * @param int|null $logoresizetoheight
     * @param bool|null $logopunchoutbackground
     * @return ResultInterface
     */
    public function build(
        ?WriterInterface $writer = null,
        ?array $writeroptions = null,
        ?bool $validateresult = null,
        // QrCode options.
        ?string $data = null,
        ?EncodingInterface $encoding = null,
        ?ErrorCorrectionLevel $errorcorrectionlevel = null,
        ?int $size = null,
        ?int $margin = null,
        ?RoundBlockSizeMode $roundblocksizemode = null,
        ?ColorInterface $foregroundcolor = null,
        ?ColorInterface $backgroundcolor = null,
        // Label options.
        ?string $labeltext = null,
        ?FontInterface $labelfont = null,
        ?LabelAlignment $labelalignment = null,
        ?MarginInterface $labelmargin = null,
        ?ColorInterface $labeltextcolor = null,
        // Logo options.
        ?string $logopath = null,
        ?int $logoresizetowidth = null,
        ?int $logoresizetoheight = null,
        ?bool $logopunchoutbackground = null,
    ): ResultInterface;
}
