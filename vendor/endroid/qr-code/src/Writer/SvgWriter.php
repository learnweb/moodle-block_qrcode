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
use Endroid\QrCode\ImageData\LogoImageData;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\Result\SvgResult;

/**
 * Writer for generating QR codes in SVG format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class SvgWriter implements WriterInterface
{
    /**
     *
     */
    public const DECIMAL_PRECISION = 2;
    /**
     *
     */
    public const WRITER_OPTION_COMPACT = 'compact';
    /**
     *
     */
    public const WRITER_OPTION_BLOCK_ID = 'block_id';
    /**
     *
     */
    public const WRITER_OPTION_EXCLUDE_XML_DECLARATION = 'exclude_xml_declaration';
    /**
     *
     */
    public const WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT = 'exclude_svg_width_and_height';
    /**
     *
     */
    public const WRITER_OPTION_FORCE_XLINK_HREF = 'force_xlink_href';

    /**
     * Writes a QR code to SVG format, optionally including a logo and label.
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
        if (!isset($options[self::WRITER_OPTION_COMPACT])) {
            $options[self::WRITER_OPTION_COMPACT] = true;
        }

        if (!isset($options[self::WRITER_OPTION_BLOCK_ID])) {
            $options[self::WRITER_OPTION_BLOCK_ID] = 'block';
        }

        if (!isset($options[self::WRITER_OPTION_EXCLUDE_XML_DECLARATION])) {
            $options[self::WRITER_OPTION_EXCLUDE_XML_DECLARATION] = false;
        }

        if (!isset($options[self::WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT])) {
            $options[self::WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT] = false;
        }

        $matrixfactory = new MatrixFactory();
        $matrix = $matrixfactory->create($qrcode);

        $xml = new \SimpleXMLElement('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"/>');
        $xml->addAttribute('version', '1.1');
        if (!$options[self::WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT]) {
            $xml->addAttribute('width', $matrix->get_outer_size() . 'px');
            $xml->addAttribute('height', $matrix->get_outer_size() . 'px');
        }
        $xml->addAttribute('viewBox', '0 0 ' . $matrix->get_outer_size() . ' ' . $matrix->get_outer_size());

        $background = $xml->addChild('rect');
        $background->addAttribute('x', '0');
        $background->addAttribute('y', '0');
        $background->addAttribute('width', strval($matrix->get_outer_size()));
        $background->addAttribute('height', strval($matrix->get_outer_size()));
        $background->addAttribute('fill', '#' .
                sprintf(
                    '%02x%02x%02x',
                    $qrcode->get_background_color()->get_red(),
                    $qrcode->get_background_color()->get_green(),
                    $qrcode->get_background_color()->get_blue()
                ));
        $background->addAttribute('fill-opacity', strval($qrcode->get_background_color()->get_opacity()));

        if ($options[self::WRITER_OPTION_COMPACT]) {
            $this->write_path($xml, $qrcode, $matrix);
        } else {
            $this->write_block_definitions($xml, $qrcode, $matrix, $options);
        }

        $result = new SvgResult($matrix, $xml, boolval($options[self::WRITER_OPTION_EXCLUDE_XML_DECLARATION]));

        if ($logo instanceof LogoInterface) {
            $this->add_logo($logo, $result, $options);
        }

        return $result;
    }

    /**
     * Writes the path for the QR code blocks in SVG format.
     *
     * @param \SimpleXMLElement $xml
     * @param QrCodeInterface $qrcode
     * @param MatrixInterface $matrix
     * @return void
     */
    private function write_path(\SimpleXMLElement $xml, QrCodeInterface $qrcode, MatrixInterface $matrix): void {
        $path = '';
        for ($rowindex = 0; $rowindex < $matrix->get_block_count(); ++$rowindex) {
            $left = $matrix->get_margin_left();
            for ($columnindex = 0; $columnindex < $matrix->get_block_count(); ++$columnindex) {
                if (1 === $matrix->get_block_value($rowindex, $columnindex)) {
                    // When we are at the first column or when the previous column was 0 set new left.
                    if (0 === $columnindex || 0 === $matrix->get_block_value($rowindex, $columnindex - 1)) {
                        $left = $matrix->get_margin_left() + $matrix->get_block_size() * $columnindex;
                    }
                    if (
                        $columnindex === $matrix->get_block_count() - 1 ||
                            0 === $matrix->get_block_value($rowindex, $columnindex + 1)
                    ) {
                        $top = $matrix->get_margin_left() + $matrix->get_block_size() * $rowindex;
                        $bottom = $matrix->get_margin_left() + $matrix->get_block_size() * ($rowindex + 1);
                        $right = $matrix->get_margin_left() + $matrix->get_block_size() * ($columnindex + 1);
                        $path .= 'M' . $this->format_number($left) . ',' . $this->format_number($top);
                        $path .= 'L' . $this->format_number($right) . ',' . $this->format_number($top);
                        $path .= 'L' . $this->format_number($right) . ',' . $this->format_number($bottom);
                        $path .= 'L' . $this->format_number($left) . ',' . $this->format_number($bottom) . 'Z';
                    }
                }
            }
        }

        $pathdefinition = $xml->addChild('path');
        $pathdefinition->addAttribute('fill', '#' .
                sprintf(
                    '%02x%02x%02x',
                    $qrcode->get_foreground_color()->get_red(),
                    $qrcode->get_foreground_color()->get_green(),
                    $qrcode->get_foreground_color()->get_blue()
                ));
        $pathdefinition->addAttribute('fill-opacity', strval($qrcode->get_foreground_color()->get_opacity()));
        $pathdefinition->addAttribute('d', $path);
    }

    /**
     * Writes the block definitions for the QR code in SVG format.
     *
     * @param \SimpleXMLElement $xml
     * @param QrCodeInterface $qrcode
     * @param MatrixInterface $matrix
     * @param array $options
     * @return void
     */
    private function write_block_definitions(
        \SimpleXMLElement $xml,
        QrCodeInterface $qrcode,
        MatrixInterface $matrix,
        array $options
    ): void {
        $xml->addChild('defs');

        $blockdefinition = $xml->defs->addChild('rect');
        $blockdefinition->addAttribute('id', strval($options[self::WRITER_OPTION_BLOCK_ID]));
        $blockdefinition->addAttribute('width', $this->format_number($matrix->get_block_size()));
        $blockdefinition->addAttribute('height', $this->format_number($matrix->get_block_size()));
        $blockdefinition->addAttribute('fill', '#' .
                sprintf(
                    '%02x%02x%02x',
                    $qrcode->get_foreground_color()->get_red(),
                    $qrcode->get_foreground_color()->get_green(),
                    $qrcode->get_foreground_color()->get_blue()
                ));
        $blockdefinition->addAttribute('fill-opacity', strval($qrcode->get_foreground_color()->get_opacity()));

        for ($rowindex = 0; $rowindex < $matrix->get_block_count(); ++$rowindex) {
            for ($columnindex = 0; $columnindex < $matrix->get_block_count(); ++$columnindex) {
                if (1 === $matrix->get_block_value($rowindex, $columnindex)) {
                    $block = $xml->addChild('use');
                    $block->addAttribute(
                        'x',
                        $this->format_number($matrix->get_margin_left() + $matrix->get_block_size() * $columnindex)
                    );
                    $block->addAttribute(
                        'y',
                        $this->format_number($matrix->get_margin_left() + $matrix->get_block_size() * $rowindex)
                    );
                    $block->addAttribute(
                        'xlink:href',
                        '#' . $options[self::WRITER_OPTION_BLOCK_ID],
                        'http://www.w3.org/1999/xlink'
                    );
                }
            }
        }
    }

    /**
     * Adds a logo to the SVG result, centered within the QR code.
     *
     * @param LogoInterface $logo
     * @param SvgResult $result
     * @param array $options
     * @return void
     * @throws \Exception
     */
    private function add_logo(LogoInterface $logo, SvgResult $result, array $options): void {
        if ($logo->get_punchout_background()) {
            throw new \Exception('The SVG writer does not support logo punchout background');
        }

        $logoimagedata = LogoImageData::create_for_logo($logo);

        if (!isset($options[self::WRITER_OPTION_FORCE_XLINK_HREF])) {
            $options[self::WRITER_OPTION_FORCE_XLINK_HREF] = false;
        }

        $xml = $result->getxml();

        /** @var \SimpleXMLElement $xmlattributes */
        $xmlattributes = $xml->attributes();

        $x = intval($xmlattributes->width) / 2 - $logoimagedata->get_width() / 2;
        $y = intval($xmlattributes->height) / 2 - $logoimagedata->get_height() / 2;

        $imagedefinition = $xml->addChild('image');
        $imagedefinition->addAttribute('x', strval($x));
        $imagedefinition->addAttribute('y', strval($y));
        $imagedefinition->addAttribute('width', strval($logoimagedata->get_width()));
        $imagedefinition->addAttribute('height', strval($logoimagedata->get_height()));
        $imagedefinition->addAttribute('preserveAspectRatio', 'none');

        if ($options[self::WRITER_OPTION_FORCE_XLINK_HREF]) {
            $imagedefinition->addAttribute('xlink:href', $logoimagedata->create_data_uri(), 'http://www.w3.org/1999/xlink');
        } else {
            $imagedefinition->addAttribute('href', $logoimagedata->create_data_uri());
        }
    }

    /**
     * Formats a floating-point number to a string with a specified decimal precision,
     * removing trailing zeros and the decimal point if necessary.
     *
     * @param float $number
     * @return string
     */
    private function format_number(float $number): string {
        $string = number_format($number, self::DECIMAL_PRECISION, '.', '');
        $string = rtrim($string, '0');

        return rtrim($string, '.');
    }
}
