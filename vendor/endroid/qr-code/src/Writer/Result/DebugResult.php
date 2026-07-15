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

namespace Endroid\QrCode\Writer\Result;

use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\QrCodeInterface;

/**
 * Represents the result of writing a QR code in debug format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class DebugResult extends AbstractResult
{
    /**
     * @var bool
     */
    private bool $validateresult = false;

    /**
     * Constructor.
     *
     * @param MatrixInterface $matrix
     * @param QrCodeInterface $qrcode
     * @param LogoInterface|null $logo
     * @param LabelInterface|null $label
     * @param array $options
     */
    public function __construct(
        MatrixInterface $matrix,
        /**
         * @var QrCodeInterface
         */
        private readonly QrCodeInterface $qrcode,
        /**
         * @var LogoInterface|null
         */
        private readonly ?LogoInterface $logo = null,
        /**
         * @var LabelInterface|null
         */
        private readonly ?LabelInterface $label = null,
        /**
         * @var array
         */
        private readonly array $options = [],
    ) {
        parent::__construct($matrix);
    }

    /**
     * Sets the validation result for the QR code.
     *
     * @param bool $validateresult
     * @return void
     */
    public function set_validate_result(bool $validateresult): void {
        $this->validateresult = $validateresult;
    }

    /**
     * Returns a string representation of the debug information for the QR code,
     * including data, encoding, error correction level, size, margin, colors, and any logo or label information.
     *
     * @return string
     */
    public function get_string(): string {
        $debuglines = [];

        $debuglines[] = 'Data: ' . $this->qrcode->get_data();
        $debuglines[] = 'Encoding: ' . $this->qrcode->get_encoding();
        $debuglines[] = 'Error Correction Level: ' . get_class($this->qrcode->get_error_correction_level());
        $debuglines[] = 'Size: ' . $this->qrcode->get_size();
        $debuglines[] = 'Margin: ' . $this->qrcode->get_margin();
        $debuglines[] = 'Round block size mode: ' . get_class($this->qrcode->get_roundblock_size_mode());
        $debuglines[] = 'Foreground color: [' . implode(', ', $this->qrcode->get_foreground_color()->to_array()) . ']';
        $debuglines[] = 'Background color: [' . implode(', ', $this->qrcode->get_background_color()->to_array()) . ']';

        foreach ($this->options as $key => $value) {
            $debuglines[] = 'Writer option: ' . $key . ': ' . $value;
        }

        if (isset($this->logo)) {
            $debuglines[] = 'Logo path: ' . $this->logo->get_path();
            $debuglines[] = 'Logo resize to width: ' . $this->logo->get_resize_to_width();
            $debuglines[] = 'Logo resize to height: ' . $this->logo->get_resize_to_height();
            $debuglines[] = 'Logo punchout background: ' . ($this->logo->get_punchout_background() ? 'true' : 'false');
        }

        if (isset($this->label)) {
            $debuglines[] = 'Label text: ' . $this->label->get_text();
            $debuglines[] = 'Label font path: ' . $this->label->get_font()->get_path();
            $debuglines[] = 'Label font size: ' . $this->label->get_font()->get_size();
            $debuglines[] = 'Label alignment: ' . get_class($this->label->get_alignment());
            $debuglines[] = 'Label margin: [' . implode(', ', $this->label->get_margin()->to_array()) . ']';
            $debuglines[] = 'Label text color: [' . implode(', ', $this->label->get_text_color()->to_array()) . ']';
        }

        $debuglines[] = 'Validate result: ' . ($this->validateresult ? 'true' : 'false');

        return implode("\n", $debuglines);
    }

    /**
     * Returns the MIME type for the debug result, which is 'text/plain'.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'text/plain';
    }
}
