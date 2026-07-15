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

namespace Endroid\QrCode\Writer;

use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Interface for writing QR codes to various formats.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface WriterInterface
{
    /**
     * Writes a QR code to a specific format, optionally including a logo and label, and returns the result.
     *
     * @param QrCodeInterface $qrcode
     * @param LogoInterface|null $logo
     * @param LabelInterface|null $label
     * @param array $options
     * @return ResultInterface
     */
    public function write(
        QrCodeInterface $qrcode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        array $options = []
    ): ResultInterface;
}
