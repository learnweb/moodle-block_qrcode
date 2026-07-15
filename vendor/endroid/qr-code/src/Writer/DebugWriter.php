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
use Endroid\QrCode\Writer\Result\DebugResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Writer for generating QR codes in debug format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class DebugWriter implements ValidatingWriterInterface, WriterInterface {
    /**
     * Writes a QR code to debug format, optionally including a logo and label.
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

        return new DebugResult($matrix, $qrcode, $logo, $label, $options);
    }

    /**
     * Validates the result of writing a QR code in debug format.
     *
     * @param ResultInterface $result
     * @param string $expecteddata
     * @return void
     * @throws \Exception
     */
    public function validate_result(ResultInterface $result, string $expecteddata): void {
        if (!$result instanceof DebugResult) {
            throw new \Exception('Unable to write logo: instance of DebugResult expected');
        }

        $result->set_validate_result(true);
    }
}
