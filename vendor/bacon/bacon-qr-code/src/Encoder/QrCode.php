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

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Mode;
use BaconQrCode\Common\Version;

/**
 * QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class QrCode
{
    /**
     * Number of possible mask patterns.
     */
    public const NUM_MASK_PATTERNS = 8;

    /**
     * Mask pattern of the QR code.
     * @var int
     */
    private int $maskpattern = -1;

    /**
     * Matrix of the QR code.
     * @var ByteMatrix
     */
    private ByteMatrix $matrix;

    /**
     * Constructor.
     *
     * @param Mode $mode
     * @param ErrorCorrectionLevel $errorcorrectionlevel
     * @param Version $version
     * @param int $maskpattern
     * @param ByteMatrix $matrix
     */
    public function __construct(
        /**
         * @var Mode
         */
        private readonly Mode $mode,
        /**
         * @var ErrorCorrectionLevel
         */
        private readonly ErrorCorrectionLevel $errorcorrectionlevel,
        /**
         * @var Version
         */
        private readonly Version $version,
        int $maskpattern,
        ByteMatrix $matrix
    ) {
        $this->maskpattern = $maskpattern;
        $this->matrix = $matrix;
    }

    /**
     * Gets the mode.
     * @return Mode
     */
    public function get_mode(): Mode {
        return $this->mode;
    }

    /**
     * Gets the EC level.
     * @return ErrorCorrectionLevel
     */
    public function get_error_correction_level(): ErrorCorrectionLevel {
        return $this->errorcorrectionlevel;
    }

    /**
     * Gets the version.
     * @return Version
     */
    public function get_version(): Version {
        return $this->version;
    }

    /**
     * Gets the mask pattern.
     * @return int
     */
    public function get_mask_pattern(): int {
        return $this->maskpattern;
    }

    /**
     * Gets the Matrix.
     * @return ByteMatrix
     */
    public function get_matrix(): ByteMatrix {
        return $this->matrix;
    }

    /**
     * Validates whether a mask pattern is valid.
     * @param int $maskpattern
     * @return bool
     */
    public static function is_valid_mask_pattern(int $maskpattern): bool {
        return $maskpattern > 0 && $maskpattern < self::NUM_MASK_PATTERNS;
    }

    /**
     * Returns a string representation of the QR code.
     * @return string
     */
    public function __toString(): string {
        $result = "<<\n"
                . ' mode: ' . $this->mode . "\n"
                . ' ecLevel: ' . $this->errorcorrectionlevel . "\n"
                . ' version: ' . $this->version . "\n"
                . ' maskPattern: ' . $this->maskpattern . "\n";

        if ($this->matrix === null) {
            $result .= " matrix: null\n";
        } else {
            $result .= " matrix:\n";
            $result .= $this->matrix;
        }

        $result .= ">>\n";

        return $result;
    }
}
