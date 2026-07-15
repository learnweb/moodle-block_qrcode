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

use BaconQrCode\Common\ErrorCorrectionLevel as BaconErrorCorrectionLevel;
use Endroid\QrCode\ErrorCorrectionLevel;

/**
 * Class for converting error correction levels between Endroid and Bacon QR code libraries.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class ErrorCorrectionLevelConverter
{
    /**
     * Converts an Endroid error correction level to a Bacon error correction level.
     *
     * @param ErrorCorrectionLevel $errorcorrectionlevel
     * @return BaconErrorCorrectionLevel
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException
     */
    public static function convert_to_bacon_error_correction_level(
        ErrorCorrectionLevel $errorcorrectionlevel
    ): BaconErrorCorrectionLevel {
        return match ($errorcorrectionlevel) {
            ErrorCorrectionLevel::Low => BaconErrorCorrectionLevel::value_of('L'),
            ErrorCorrectionLevel::Medium => BaconErrorCorrectionLevel::value_of('M'),
            ErrorCorrectionLevel::Quartile => BaconErrorCorrectionLevel::value_of('Q'),
            ErrorCorrectionLevel::High => BaconErrorCorrectionLevel::value_of('H'),
        };
    }
}
