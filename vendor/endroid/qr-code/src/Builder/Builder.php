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

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Encoding\EncodingInterface;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Font\FontInterface;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Label\Margin\MarginInterface;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\ValidatingWriterInterface;
use Endroid\QrCode\Writer\WriterInterface;

/**
 * Builder for creating QR codes with optional labels and logos.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class Builder implements BuilderInterface
{
    /**
     * Constructor.
     *
     * @param WriterInterface $writer
     * @param array $writeroptions
     * @param bool $validateresult
     * @param string $data
     * @param EncodingInterface $encoding
     * @param ErrorCorrectionLevel $errorcorrectionlevel
     * @param int $size
     * @param int $margin
     * @param RoundBlockSizeMode $roundblocksizemode
     * @param ColorInterface $foregroundcolor
     * @param ColorInterface $backgroundcolor
     * @param string $labeltext
     * @param FontInterface $labelfont
     * @param LabelAlignment $labelalignment
     * @param MarginInterface $labelmargin
     * @param ColorInterface $labeltextcolor
     * @param string $logopath
     * @param int|null $logoresizetowidth
     * @param int|null $logoresizetoheight
     * @param bool $logopunchoutbackground
     */
    public function __construct(
        /**
         * @var WriterInterface
         */
        private WriterInterface $writer = new PngWriter(),
        /** @var array<mixed> */
        /**
         * @var array
         */
        private array $writeroptions = [],
        /**
         * @var bool
         */
        private bool $validateresult = false,
        /**
         * @var string
         */
        private string $data = '',
        /**
         * @var EncodingInterface|Encoding
         */
        private EncodingInterface $encoding = new Encoding('UTF-8'),
        /**
         * @var ErrorCorrectionLevel
         */
        private ErrorCorrectionLevel $errorcorrectionlevel = ErrorCorrectionLevel::Low,
        /**
         * @var int
         */
        private int $size = 300,
        /**
         * @var int
         */
        private int $margin = 10,
        /**
         * @var RoundBlockSizeMode
         */
        private RoundBlockSizeMode $roundblocksizemode = RoundBlockSizeMode::Margin,
        /**
         * @var ColorInterface|Color
         */
        private ColorInterface $foregroundcolor = new Color(0, 0, 0),
        /**
         * @var ColorInterface|Color
         */
        private ColorInterface $backgroundcolor = new Color(255, 255, 255),
        // Label options.
        /**
         * @var string
         */
        private string $labeltext = '',
        /**
         * @var FontInterface|Font
         */
        private FontInterface $labelfont = new Font(__DIR__ . '/../../assets/open_sans.ttf', 16),
        /**
         * @var LabelAlignment
         */
        private LabelAlignment $labelalignment = LabelAlignment::Center,
        /**
         * @var MarginInterface|Margin
         */
        private MarginInterface $labelmargin = new Margin(0, 10, 10, 10),
        /**
         * @var ColorInterface|Color
         */
        private ColorInterface $labeltextcolor = new Color(0, 0, 0),
        // Logo options.
        /**
         * @var string
         */
        private string $logopath = '',
        /**
         * @var int|null
         */
        private ?int $logoresizetowidth = null,
        /**
         * @var int|null
         */
        private ?int $logoresizetoheight = null,
        /**
         * @var bool
         */
        private bool $logopunchoutbackground = false,
    ) {
    }

    /**
     * Builds the QR code with the specified options and returns the result.
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
     * @throws ValidationException
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
    ): ResultInterface {
        if ($this->validateresult && !$this->writer instanceof ValidatingWriterInterface) {
            throw ValidationException::create_for_unsupported_writer(get_class($this->writer));
        }

        $writer = $writer ?? $this->writer;
        $writeroptions = $writeroptions ?? $this->writeroptions;
        $validateresult = $validateresult ?? $this->validateresult;

        $createlabel = $this->labeltext || $labeltext;
        $createlogo = $this->logopath || $logopath;

        $qrcode = new QrCode(
            data: $data ?? $this->data,
            encoding: $encoding ?? $this->encoding,
            errorcorrectionlevel: $errorcorrectionlevel ?? $this->errorcorrectionlevel,
            size: $size ?? $this->size,
            margin: $margin ?? $this->margin,
            roundblocksizemode: $roundblocksizemode ?? $this->roundblocksizemode,
            foregroundcolor: $foregroundcolor ?? $this->foregroundcolor,
            backgroundcolor: $backgroundcolor ?? $this->backgroundcolor
        );

        $logo = $createlogo ? new Logo(
            path: $logopath ?? $this->logopath,
            resizetowidth: $logoresizetowidth ?? $this->logoresizetowidth,
            resizetoheight: $logoresizetoheight ?? $this->logoresizetoheight,
            punchoutbackground: $logopunchoutbackground ?? $this->logopunchoutbackground
        ) : null;

        $label = $createlabel ? new Label(
            text: $labeltext ?? $this->labeltext,
            font: $labelfont ?? $this->labelfont,
            alignment: $labelalignment ?? $this->labelalignment,
            margin: $labelmargin ?? $this->labelmargin,
            textcolor: $labeltextcolor ?? $this->labeltextcolor
        ) : null;

        $result = $writer->write($qrcode, $logo, $label, $writeroptions);

        if ($validateresult && $writer instanceof ValidatingWriterInterface) {
            $writer->validate_result($result, $qrcode->get_data());
        }

        return $result;
    }
}
