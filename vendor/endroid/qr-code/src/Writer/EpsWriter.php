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
use Endroid\QrCode\Writer\Result\EpsResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Writer for generating QR codes in EPS format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class EpsWriter implements WriterInterface
{
    /**
     * Decimal Precision 10.
     */
    public const DECIMAL_PRECISION = 10;

    /**
     * Writes a QR code to EPS format, optionally including a logo and label, and returns the result.
     *
     * @param QrCodeInterface $qrcode
     * @param LogoInterface|null $logo
     * @param LabelInterface|null $label
     * @param array $options
     * @return ResultInterface
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException
     * @throws \Endroid\QrCode\Exception\BlockSizeTooSmallException
     */
    public function write(
        QrCodeInterface $qrcode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        array $options = []
    ): ResultInterface {
        $matrixfactory = new MatrixFactory();
        $matrix = $matrixfactory->create($qrcode);

        $lines = [
            '%!PS-Adobe-3.0 EPSF-3.0',
            '%%BoundingBox: 0 0 ' . $matrix->get_outer_size() . ' ' . $matrix->get_outer_size(),
            '/F { rectfill } def',
                number_format($qrcode->get_background_color()->get_red() / 100, 2, '.', ',') . ' ' .
                number_format($qrcode->get_background_color()->get_green() / 100, 2, '.', ',') . ' ' .
                number_format($qrcode->get_background_color()->get_blue() / 100, 2, '.', ',') . ' setrgbcolor',
            '0 0 ' . $matrix->get_outer_size() . ' ' . $matrix->get_outer_size() . ' F',
                number_format($qrcode->get_foreground_color()->get_red() / 100, 2, '.', ',') . ' ' .
                number_format($qrcode->get_foreground_color()->get_green() / 100, 2, '.', ',') . ' ' .
                number_format($qrcode->get_foreground_color()->get_blue() / 100, 2, '.', ',') . ' setrgbcolor',
        ];

        for ($rowindex = 0; $rowindex < $matrix->get_block_count(); ++$rowindex) {
            for ($columnindex = 0; $columnindex < $matrix->get_block_count(); ++$columnindex) {
                if (1 === $matrix->get_block_value($matrix->get_block_count() - 1 - $rowindex, $columnindex)) {
                    $x = $matrix->get_margin_left() + $matrix->get_block_size() * $columnindex;
                    $y = $matrix->get_margin_left() + $matrix->get_block_size() * $rowindex;
                    $lines[] = number_format($x, self::DECIMAL_PRECISION, '.', '') . ' ' .
                            number_format($y, self::DECIMAL_PRECISION, '.', '') . ' ' .
                            number_format($matrix->get_block_size(), self::DECIMAL_PRECISION, '.', '') . ' ' .
                            number_format($matrix->get_block_size(), self::DECIMAL_PRECISION, '.', '') . ' F';
                }
            }
        }

        return new EpsResult($matrix, $lines);
    }
}
