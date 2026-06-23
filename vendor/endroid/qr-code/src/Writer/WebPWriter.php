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
use Endroid\QrCode\Writer\Result\GdResult;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\Result\WebPResult;

final readonly class WebPWriter extends AbstractGdWriter
{
    public const WRITER_OPTION_QUALITY = 'quality';

    public function write(QrCodeInterface $qrCode, ?LogoInterface $logo = null, ?LabelInterface $label = null, array $options = []): ResultInterface {
        if (!isset($options[self::WRITER_OPTION_QUALITY])) {
            $options[self::WRITER_OPTION_QUALITY] = -1;
        }

        /** @var GdResult $gdResult */
        $gdResult = parent::write($qrCode, $logo, $label, $options);

        return new WebPResult($gdResult->getMatrix(), $gdResult->getImage(), $options[self::WRITER_OPTION_QUALITY]);
    }
}
