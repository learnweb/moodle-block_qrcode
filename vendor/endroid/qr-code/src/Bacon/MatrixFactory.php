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

namespace Endroid\QrCode\Bacon;

defined('MOODLE_INTERNAL') || die();

use BaconQrCode\Encoder\Encoder;
use Endroid\QrCode\Matrix\Matrix;
use Endroid\QrCode\Matrix\MatrixFactoryInterface;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\QrCodeInterface;

/**
 * Factory for creating a matrix of blocks for a QR code using the BaconQrCode library.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class MatrixFactory implements MatrixFactoryInterface
{
    /**
     * Creates a matrix of blocks for a QR code using the BaconQrCode library.
     *
     * @param QrCodeInterface $qrcode
     * @return MatrixInterface
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException
     * @throws \Endroid\QrCode\Exception\BlockSizeTooSmallException
     */
    public function create(QrCodeInterface $qrcode): MatrixInterface {
        $baconerrorcorrectionlevel =
                ErrorCorrectionLevelConverter::convert_to_bacon_error_correction_level($qrcode->get_error_correction_level());
        $baconmatrix =
                Encoder::encode($qrcode->get_data(), $baconerrorcorrectionlevel, strval($qrcode->get_encoding()))->get_matrix();

        $blockvalues = [];
        $columncount = $baconmatrix->get_width();
        $rowcount = $baconmatrix->get_height();
        for ($rowindex = 0; $rowindex < $rowcount; ++$rowindex) {
            $blockvalues[$rowindex] = [];
            for ($columnindex = 0; $columnindex < $columncount; ++$columnindex) {
                $blockvalues[$rowindex][$columnindex] = $baconmatrix->get($columnindex, $rowindex);
            }
        }

        return new Matrix($blockvalues, $qrcode->get_size(), $qrcode->get_margin(), $qrcode->get_roundblock_size_mode());
    }
}
