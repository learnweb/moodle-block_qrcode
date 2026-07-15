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

/**
 * BaconQrCode.
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace BaconQrCode\Common;

/**
 * Encapsulates a QR Code's format information, including the data mask used and error correction level.
 */
class FormatInformation
{
    /**
     * Mask for format information.
     */
    private const FORMAT_INFO_MASK_QR = 0x5412;

    /**
     * Lookup table for decoding format information.
     *
     * See ISO 18004:2006, Annex C, Table C.1
     */
    private const FORMAT_INFO_DECODE_LOOKUP = [
        [0x5412, 0x00],
        [0x5125, 0x01],
        [0x5e7c, 0x02],
        [0x5b4b, 0x03],
        [0x45f9, 0x04],
        [0x40ce, 0x05],
        [0x4f97, 0x06],
        [0x4aa0, 0x07],
        [0x77c4, 0x08],
        [0x72f3, 0x09],
        [0x7daa, 0x0a],
        [0x789d, 0x0b],
        [0x662f, 0x0c],
        [0x6318, 0x0d],
        [0x6c41, 0x0e],
        [0x6976, 0x0f],
        [0x1689, 0x10],
        [0x13be, 0x11],
        [0x1ce7, 0x12],
        [0x19d0, 0x13],
        [0x0762, 0x14],
        [0x0255, 0x15],
        [0x0d0c, 0x16],
        [0x083b, 0x17],
        [0x355f, 0x18],
        [0x3068, 0x19],
        [0x3f31, 0x1a],
        [0x3a06, 0x1b],
        [0x24b4, 0x1c],
        [0x2183, 0x1d],
        [0x2eda, 0x1e],
        [0x2bed, 0x1f],
    ];

    /**
     * Offset i holds the number of 1 bits in the binary representation of i.
     *
     * @var int[]
     */
    private const BITS_SET_IN_HALF_BYTE = [0, 1, 1, 2, 1, 2, 2, 3, 1, 2, 2, 3, 2, 3, 3, 4];

    /**
     * Error correction level.
     *
     * @var ErrorCorrectionLevel
     */
    private ErrorCorrectionLevel $eclevel;

    /**
     * @var int
     */
    private int $datamask;

    /**
     * Creates a new format information instance.
     *
     * @param int $formatinfo encoded format information
     */
    protected function __construct(int $formatinfo) {
        $this->eclevel = ErrorCorrectionLevel::for_bits(($formatinfo >> 3) & 0x3);
        $this->datamask = $formatinfo & 0x7;
    }

    /**
     * Checks how many bits are different between two integers.
     */
    public static function num_bits_differing(int $a, int $b): int {
        $a ^= $b;

        return (
            self::BITS_SET_IN_HALF_BYTE[$a & 0xf]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 4) & 0xf)]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 8) & 0xf)]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 12) & 0xf)]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 16) & 0xf)]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 20) & 0xf)]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 24) & 0xf)]
            + self::BITS_SET_IN_HALF_BYTE[(BitUtils::unsignedrightshift($a, 28) & 0xf)]
        );
    }

    /**
     * Decodes format information.
     */
    public static function decode_format_information(int $maskedformatinfo1, int $maskedformatinfo2): ?self {
        $formatinfo = self::do_decode_format_information($maskedformatinfo1, $maskedformatinfo2);

        if (null !== $formatinfo) {
            return $formatinfo;
        }

        // Should return null, but, some QR codes apparently do not mask this info. Try again by actually masking the pattern first.
        return self::do_decode_format_information(
            $maskedformatinfo1 ^ self::FORMAT_INFO_MASK_QR,
            $maskedformatinfo2 ^ self::FORMAT_INFO_MASK_QR
        );
    }

    /**
     * Internal method for decoding format information.
     */
    private static function do_decode_format_information(int $maskedformatinfo1, int $maskedformatinfo2): ?self {
        $bestdifference = PHP_INT_MAX;
        $bestformatinfo = 0;

        foreach (self::FORMAT_INFO_DECODE_LOOKUP as $decodeinfo) {
            $targetinfo = $decodeinfo[0];

            if ($targetinfo === $maskedformatinfo1 || $targetinfo === $maskedformatinfo2) {
                // Found an exact match.
                return new self($decodeinfo[1]);
            }

            $bitsdifference = self::num_bits_differing($maskedformatinfo1, $targetinfo);

            if ($bitsdifference < $bestdifference) {
                $bestformatinfo = $decodeinfo[1];
                $bestdifference = $bitsdifference;
            }

            if ($maskedformatinfo1 !== $maskedformatinfo2) {
                // Also try the other option.
                $bitsdifference = self::num_bits_differing($maskedformatinfo2, $targetinfo);

                if ($bitsdifference < $bestdifference) {
                    $bestformatinfo = $decodeinfo[1];
                    $bestdifference = $bitsdifference;
                }
            }
        }

        // Hamming distance of the 32 masked codes is 7, by construction, so <= 3 bits differing means we found a match.
        if ($bestdifference <= 3) {
            return new self($bestformatinfo);
        }

        return null;
    }

    /**
     * Returns the error correction level.
     */
    public function get_error_correction_level(): ErrorCorrectionLevel {
        return $this->eclevel;
    }

    /**
     * Returns the data mask.
     */
    public function get_data_mask(): int {
        return $this->datamask;
    }

    /**
     * Hashes the code of the EC level.
     */
    public function hash_code(): int {
        return ($this->eclevel->get_bits() << 3) | $this->datamask;
    }

    /**
     * Verifies if this instance equals another one.
     */
    public function equals(self $other): bool {
        return (
            $this->eclevel === $other->eclevel
            && $this->datamask === $other->datamask
        );
    }
}
