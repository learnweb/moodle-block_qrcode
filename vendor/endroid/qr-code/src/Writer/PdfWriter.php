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
use Endroid\QrCode\Writer\Result\PdfResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Writer for generating QR codes in PDF format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class PdfWriter implements WriterInterface
{
    /**
     *
     */
    public const WRITER_OPTION_UNIT = 'unit';
    /**
     *
     */
    public const WRITER_OPTION_PDF = 'fpdf';
    /**
     *
     */
    public const WRITER_OPTION_X = 'x';
    /**
     *
     */
    public const WRITER_OPTION_Y = 'y';
    /**
     *
     */
    public const WRITER_OPTION_LINK = 'link';

    /**
     * Writes a QR code to PDF format, optionally including a logo and label, and returns the result.
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

        $unit = 'mm';
        if (isset($options[self::WRITER_OPTION_UNIT])) {
            $unit = $options[self::WRITER_OPTION_UNIT];
        }

        $allowedunits = ['mm', 'pt', 'cm', 'in'];
        if (!in_array($unit, $allowedunits)) {
            throw new \Exception(sprintf('PDF Measure unit should be one of [%s]', implode(', ', $allowedunits)));
        }

        $labelspace = 0;
        if ($label instanceof LabelInterface) {
            $labelspace = 30;
        }

        if (!class_exists(\FPDF::class)) {
            throw new \Exception('Unable to find FPDF: check your installation');
        }

        $foregroundcolor = $qrcode->get_foreground_color();
        if ($foregroundcolor->get_alpha() > 0) {
            throw new \Exception('PDF Writer does not support alpha channels');
        }
        $backgroundcolor = $qrcode->get_background_color();
        if ($backgroundcolor->get_alpha() > 0) {
            throw new \Exception('PDF Writer does not support alpha channels');
        }

        if (isset($options[self::WRITER_OPTION_PDF])) {
            $fpdf = $options[self::WRITER_OPTION_PDF];
            if (!$fpdf instanceof \FPDF) {
                throw new \Exception('pdf option must be an instance of FPDF');
            }
        } else {
            // Todo: @todo Check how to add label height later.
            $fpdf = new \FPDF('P', $unit, [$matrix->get_outer_size(), $matrix->get_outer_size() + $labelspace]);
            $fpdf->AddPage();
        }

        $x = 0;
        if (isset($options[self::WRITER_OPTION_X])) {
            $x = $options[self::WRITER_OPTION_X];
        }
        $y = 0;
        if (isset($options[self::WRITER_OPTION_Y])) {
            $y = $options[self::WRITER_OPTION_Y];
        }

        $fpdf->SetFillColor($backgroundcolor->get_red(), $backgroundcolor->get_green(), $backgroundcolor->get_blue());
        $fpdf->Rect($x, $y, $matrix->get_outer_size(), $matrix->get_outer_size(), 'F');
        $fpdf->SetFillColor($foregroundcolor->get_red(), $foregroundcolor->get_green(), $foregroundcolor->get_blue());

        for ($rowindex = 0; $rowindex < $matrix->get_block_count(); ++$rowindex) {
            for ($columnindex = 0; $columnindex < $matrix->get_block_count(); ++$columnindex) {
                if (1 === $matrix->get_block_value($rowindex, $columnindex)) {
                    $fpdf->Rect(
                        $x + $matrix->get_margin_left() + ($columnindex * $matrix->get_block_size()),
                        $y + $matrix->get_margin_left() + ($rowindex * $matrix->get_block_size()),
                        $matrix->get_block_size(),
                        $matrix->get_block_size(),
                        'F'
                    );
                }
            }
        }

        if ($logo instanceof LogoInterface) {
            $this->add_logo($logo, $fpdf, $x, $y, $matrix->get_outer_size());
        }

        if ($label instanceof LabelInterface) {
            $fpdf->SetXY($x, $y + $matrix->get_outer_size() + $labelspace - 25);
            $fpdf->SetFont('Helvetica', '', $label->get_font()->get_size());
            $fpdf->Cell($matrix->get_outer_size(), 0, $label->get_text(), 0, 0, 'C');
        }

        if (isset($options[self::WRITER_OPTION_LINK])) {
            $link = $options[self::WRITER_OPTION_LINK];
            $fpdf->Link($x, $y, $x + $matrix->get_outer_size(), $y + $matrix->get_outer_size(), $link);
        }

        return new PdfResult($matrix, $fpdf);
    }

    /**
     * Adds a logo to the PDF at the specified position and size.
     *
     * @param LogoInterface $logo
     * @param \FPDF $fpdf
     * @param float $x
     * @param float $y
     * @param float $size
     * @return void
     * @throws \Exception
     */
    private function add_logo(LogoInterface $logo, \FPDF $fpdf, float $x, float $y, float $size): void {
        $logopath = $logo->get_path();
        $logoheight = $logo->get_resize_to_height();
        $logowidth = $logo->get_resize_to_width();

        if (null === $logoheight || null === $logowidth) {
            $imagesize = \getimagesize($logopath);
            if (!$imagesize) {
                throw new \Exception(sprintf('Unable to read image size for logo "%s"', $logopath));
            }
            [$logosourcewidth, $logosourceheight] = $imagesize;

            if (null === $logowidth) {
                $logowidth = (int) $logosourcewidth;
            }

            if (null === $logoheight) {
                $aspectratio = $logowidth / $logosourcewidth;
                $logoheight = (int) ($logosourceheight * $aspectratio);
            }
        }

        $logox = $x + $size / 2 - $logowidth / 2;
        $logoy = $y + $size / 2 - $logoheight / 2;

        $fpdf->Image($logopath, $logox, $logoy, $logowidth, $logoheight);
    }
}
