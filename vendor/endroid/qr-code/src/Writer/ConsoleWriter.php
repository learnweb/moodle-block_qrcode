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

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Bacon\MatrixFactory;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\ConsoleResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Writer for generating QR codes in console format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class ConsoleWriter implements WriterInterface
{
    /**
     * Writes a QR code to console format, optionally including a logo and label, and returns the result.
     *
     * @param QrCodeInterface $qrcode
     * @param LogoInterface|null $logo
     * @param LabelInterface|null $label
     * @param $options
     * @return ResultInterface
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException
     * @throws \Endroid\QrCode\Exception\BlockSizeTooSmallException
     */
    public function write(
        QrCodeInterface $qrcode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        $options = []
    ): ResultInterface {
        $matrixfactory = new MatrixFactory();
        $matrix = $matrixfactory->create($qrcode);

        return new ConsoleResult($matrix, $qrcode->get_foreground_color(), $qrcode->get_background_color());
    }
}
