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
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\ImageData\LabelImageData;
use Endroid\QrCode\ImageData\LogoImageData;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\Result\GdResult;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Zxing\QrReader;

/**
 * Abstract class for GD-based QR code writers,
 * providing common functionality for generating QR codes in various image formats using the GD extension.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract readonly class AbstractGdWriter implements ValidatingWriterInterface, WriterInterface {
    /**
     * Generates a matrix representation of the QR code using the MatrixFactory.
     *
     * @param QrCodeInterface $qrcode
     * @return MatrixInterface
     */
    protected function get_matrix(QrCodeInterface $qrcode): MatrixInterface {
        $matrixfactory = new MatrixFactory();

        return $matrixfactory->create($qrcode);
    }

    /**
     * Writes a QR code to an image format using the GD extension, optionally including a logo and label.
     *
     * @param QrCodeInterface $qrcode
     * @param LogoInterface|null $logo
     * @param LabelInterface|null $label
     * @param array $options
     * @return ResultInterface
     * @throws \Exception
     */
    public function write(
        QrCodeInterface $qrcode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        array $options = []
    ): ResultInterface {
        if (!extension_loaded('gd')) {
            throw new \Exception('Unable to generate image: please check if the GD extension is enabled and configured correctly');
        }

        $matrix = $this->get_matrix($qrcode);

        $baseblocksize = RoundBlockSizeMode::None === $qrcode->get_roundblock_size_mode() ? 10 : intval($matrix->get_block_size());
        $baseimage = imagecreatetruecolor($matrix->get_block_count() * $baseblocksize, $matrix->get_block_count() * $baseblocksize);

        if (!$baseimage) {
            throw new \Exception('Unable to generate image: please check if the GD extension is enabled and configured correctly');
        }

        /** @var int $foregroundcolor */
        $foregroundcolor = imagecolorallocatealpha(
            $baseimage,
            $qrcode->get_foreground_color()->get_red(),
            $qrcode->get_foreground_color()->get_green(),
            $qrcode->get_foreground_color()->get_blue(),
            $qrcode->get_foreground_color()->get_alpha()
        );

        /** @var int $transparentcolor */
        $transparentcolor = imagecolorallocatealpha($baseimage, 255, 255, 255, 127);

        imagefill($baseimage, 0, 0, $transparentcolor);

        for ($rowindex = 0; $rowindex < $matrix->get_block_count(); ++$rowindex) {
            for ($columnindex = 0; $columnindex < $matrix->get_block_count(); ++$columnindex) {
                if (1 === $matrix->get_block_value($rowindex, $columnindex)) {
                    imagefilledrectangle(
                        $baseimage,
                        $columnindex * $baseblocksize,
                        $rowindex * $baseblocksize,
                        ($columnindex + 1) * $baseblocksize - 1,
                        ($rowindex + 1) * $baseblocksize - 1,
                        $foregroundcolor
                    );
                }
            }
        }

        $targetwidth = $matrix->get_outer_size();
        $targetheight = $matrix->get_outer_size();

        if ($label instanceof LabelInterface) {
            $labelimagedata = LabelImageData::create_for_label($label);
            $targetheight += $labelimagedata->get_height() + $label->get_margin()->get_top() + $label->get_margin()->get_bottom();
        }

        $targetimage = imagecreatetruecolor($targetwidth, $targetheight);

        if (!$targetimage) {
            throw new \Exception('Unable to generate image: please check if the GD extension is enabled and configured correctly');
        }

        /** @var int $backgroundcolor */
        $backgroundcolor = imagecolorallocatealpha(
            $targetimage,
            $qrcode->get_background_color()->get_red(),
            $qrcode->get_background_color()->get_green(),
            $qrcode->get_background_color()->get_blue(),
            $qrcode->get_background_color()->get_alpha()
        );

        imagefill($targetimage, 0, 0, $backgroundcolor);

        imagecopyresampled(
            $targetimage,
            $baseimage,
            $matrix->get_margin_left(),
            $matrix->get_margin_left(),
            0,
            0,
            $matrix->get_inner_size(),
            $matrix->get_inner_size(),
            imagesx($baseimage),
            imagesy($baseimage)
        );

        if ($qrcode->get_background_color()->get_alpha() > 0) {
            imagesavealpha($targetimage, true);
        }

        $result = new GdResult($matrix, $targetimage);

        if ($logo instanceof LogoInterface) {
            $result = $this->add_logo($logo, $result);
        }

        if ($label instanceof LabelInterface) {
            $result = $this->add_label($label, $result);
        }

        return $result;
    }

    /**
     * Adds a logo to the QR code image, handling resizing and optional punchout of the background.
     *
     * @param LogoInterface $logo
     * @param GdResult $result
     * @return GdResult
     * @throws \Exception
     */
    private function add_logo(LogoInterface $logo, GdResult $result): GdResult {
        $logoimagedata = LogoImageData::create_for_logo($logo);

        if ('image/svg+xml' === $logoimagedata->get_mime_type()) {
            throw new \Exception('PNG Writer does not support SVG logo');
        }

        $targetimage = $result->get_image();
        $matrix = $result->get_matrix();

        if ($logoimagedata->get_punchout_background()) {
            /** @var int $transparent */
            $transparent = imagecolorallocatealpha($targetimage, 255, 255, 255, 127);
            imagealphablending($targetimage, false);
            $xoffsetstart = intval($matrix->get_outer_size() / 2 - $logoimagedata->get_width() / 2);
            $yoffsetstart = intval($matrix->get_outer_size() / 2 - $logoimagedata->get_height() / 2);
            for ($xoffset = $xoffsetstart; $xoffset < $xoffsetstart + $logoimagedata->get_width(); ++$xoffset) {
                for ($yoffset = $yoffsetstart; $yoffset < $yoffsetstart + $logoimagedata->get_height(); ++$yoffset) {
                    imagesetpixel($targetimage, $xoffset, $yoffset, $transparent);
                }
            }
        }

        imagecopyresampled(
            $targetimage,
            $logoimagedata->get_image(),
            intval($matrix->get_outer_size() / 2 - $logoimagedata->get_width() / 2),
            intval($matrix->get_outer_size() / 2 - $logoimagedata->get_height() / 2),
            0,
            0,
            $logoimagedata->get_width(),
            $logoimagedata->get_height(),
            imagesx($logoimagedata->get_image()),
            imagesy($logoimagedata->get_image())
        );

        return new GdResult($matrix, $targetimage);
    }

    /**
     * Adds a label to the QR code image, positioning it according to the specified alignment and margins.
     *
     * @param LabelInterface $label
     * @param GdResult $result
     * @return GdResult
     * @throws \Exception
     */
    private function add_label(LabelInterface $label, GdResult $result): GdResult {
        $targetimage = $result->get_image();

        $labelimagedata = LabelImageData::create_for_label($label);

        /** @var int $textcolor */
        $textcolor = imagecolorallocatealpha(
            $targetimage,
            $label->get_text_color()->get_red(),
            $label->get_text_color()->get_green(),
            $label->get_text_color()->get_blue(),
            $label->get_text_color()->get_alpha()
        );

        $x = intval(imagesx($targetimage) / 2 - $labelimagedata->get_width() / 2);
        $y = imagesy($targetimage) - $label->get_margin()->get_bottom();

        if (LabelAlignment::Left === $label->get_alignment()) {
            $x = $label->get_margin()->get_left();
        } else if (LabelAlignment::Right === $label->get_alignment()) {
            $x = imagesx($targetimage) - $labelimagedata->get_width() - $label->get_margin()->get_right();
        }

        imagettftext(
            $targetimage,
            $label->get_font()->get_size(),
            0,
            $x,
            $y,
            $textcolor,
            $label->get_font()->get_path(),
            $label->get_text()
        );

        return new GdResult($result->get_matrix(), $targetimage);
    }

    /**
     * Validates the generated QR code result by decoding it and comparing it to the expected data.
     * If the decoded text does not match the expected data, a ValidationException is thrown.
     *
     * @param ResultInterface $result
     * @param string $expecteddata
     * @return void
     * @throws ValidationException
     */
    public function validate_result(ResultInterface $result, string $expecteddata): void {
        $string = $result->get_string();

        if (!class_exists(QrReader::class)) {
            throw ValidationException::create_for_missing_package('khanamiryan/qrcode-detector-decoder');
        }

        $reader = new QrReader($string, QrReader::SOURCE_TYPE_BLOB);
        if ($reader->text() !== $expecteddata) {
            throw ValidationException::create_for_invalid_data($expecteddata, strval($reader->text()));
        }
    }
}
