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

use BaconQrCode\Common\BitArray;
use BaconQrCode\Common\CharacterSetEci;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Mode;
use BaconQrCode\Common\ReedSolomonCodec;
use BaconQrCode\Common\Version;
use BaconQrCode\Exception\WriterException;
use SplFixedArray;

/**
 * Encoder.
 *
 * @copyright  2017 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Encoder
{
    /**
     * Default byte encoding.
     */
    public const DEFAULT_BYTE_MODE_ENCODING = 'ISO-8859-1';

    /** @deprecated use DEFAULT_BYTE_MODE_ENCODING */
    public const DEFAULT_BYTE_MODE_ECODING = self::DEFAULT_BYTE_MODE_ENCODING;

    /**
     * The original table is defined in the table 5 of JISX0510:2004 (p.19).
     */
    private const ALPHANUMERIC_TABLE = [
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, // 0x00-0x0f.
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, // 0x10-0x1f.
        36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43, // 0x20-0x2f.
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 44, -1, -1, -1, -1, -1, // 0x30-0x3f.
        -1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, // 0x40-0x4f.
        25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1, // 0x50-0x5f.
    ];

    /**
     * Codec cache.
     *
     * @var array<string,ReedSolomonCodec>
     */
    private static array $codecs = [];

    /**
     * Encodes "content" with the error correction level "ecLevel".
     */
    public static function encode(
        string $content,
        ErrorCorrectionLevel $eclevel,
        string $encoding = self::DEFAULT_BYTE_MODE_ENCODING,
        ?Version $forcedversion = null,
        // Barcode scanner might not be able to read the encoded message of the QR code with the prefix ECI of UTF-8.
        bool $prefixeci = true
    ): QrCode {
        /* Pick an encoding mode appropriate for the content. Note that this
        will not attempt to use multiple modes / segments even if that were
        more efficient. Would be nice. */
        $mode = self::choose_mode($content, $encoding);

        /* This will store the header information, like mode and length, as well
        as "header" segments like an ECI segment. */
        $headerbits = new BitArray();

        // Append ECI segment if applicable.
        if ($prefixeci && Mode::BYTE() === $mode && self::DEFAULT_BYTE_MODE_ENCODING !== $encoding) {
            $eci = CharacterSetEci::getcharactersetecibyname($encoding);

            if (null !== $eci) {
                self::append_eci($eci, $headerbits);
            }
        }

        // Write the mode marker (with ECI in place).
        self::append_mode_info($mode, $headerbits);

        // Collect data within the main segment, separately, to count its size if needed. Don't add it to main payload yet.
        $databits = new BitArray();
        self::append_bytes($content, $mode, $databits, $encoding);

        /* Hard part: need to know version to know how many bits length takes.
        But need to know how many bits it takes to know version. First we
        take a guess at version by assuming version will be the minimum, 1: */
        $provisionalbitsneeded = $headerbits->get_size()
            + $mode->get_character_count_bits(Version::get_version_for_number(1))
            + $databits->get_size();
        $provisionalversion = self::choose_version($provisionalbitsneeded, $eclevel);

        // Use that guess to calculate the right version. I am still not sure if this works in 100% of cases.
        $bitsneeded = $headerbits->get_size()
            + $mode->get_character_count_bits($provisionalversion)
            + $databits->get_size();
        $version = self::choose_version($bitsneeded, $eclevel);

        if (null !== $forcedversion) {
            // Forced version check.
            if ($version->get_version_number() <= $forcedversion->get_version_number()) {
                // Calculated minimum version is same or equal as forced version.
                $version = $forcedversion;
            } else {
                throw new WriterException(
                    'Invalid version! Calculated version: '
                    . $version->get_version_number()
                    . ', requested version: '
                    . $forcedversion->get_version_number()
                );
            }
        }

        $headeranddatabits = new BitArray();
        $headeranddatabits->append_bit_array($headerbits);

        // Find "length" of main segment and write it.
        $numletters = (Mode::BYTE() === $mode ? $databits->get_size_in_bytes() : strlen($content));
        self::append_length_info($numletters, $version, $mode, $headeranddatabits);

        // Put data together into the overall payload.
        $headeranddatabits->append_bit_array($databits);
        $ecblocks = $version->get_ecblocks_for_level($eclevel);
        $numdatabytes = $version->get_total_codewords() - $ecblocks->get_total_ec_codewords();

        // Terminate the bits properly.
        self::terminate_bits($numdatabytes, $headeranddatabits);

        // Interleave data bits with error correction code.
        $finalbits = self::interleave_with_ec_bytes(
            $headeranddatabits,
            $version->get_total_codewords(),
            $numdatabytes,
            $ecblocks->get_num_blocks()
        );

        // Choose the mask pattern.
        $dimension = $version->get_dimension_for_version();
        $matrix = new ByteMatrix($dimension, $dimension);
        $maskpattern = self::choose_mask_pattern($finalbits, $eclevel, $version, $matrix);

        // Build the matrix.
        MatrixUtil::buildmatrix($finalbits, $eclevel, $version, $maskpattern, $matrix);

        return new QrCode($mode, $eclevel, $version, $maskpattern, $matrix);
    }

    /**
     * Gets the alphanumeric code for a byte.
     */
    private static function get_alphanumeric_code(int $code): int {
        if (isset(self::ALPHANUMERIC_TABLE[$code])) {
            return self::ALPHANUMERIC_TABLE[$code];
        }

        return -1;
    }

    /**
     * Chooses the best mode for a given content.
     */
    private static function choose_mode(string $content, ?string $encoding = null): Mode {
        if (null !== $encoding && 0 === strcasecmp($encoding, 'SHIFT-JIS')) {
            return self::is_only_double_byte_kanji($content) ? Mode::KANJI() : Mode::BYTE();
        }

        $hasnumeric = false;
        $hasalphanumeric = false;
        $contentlength = strlen($content);

        for ($i = 0; $i < $contentlength; ++$i) {
            $char = $content[$i];

            if (ctype_digit($char)) {
                $hasnumeric = true;
            } else if (-1 !== self::get_alphanumeric_code(ord($char))) {
                $hasalphanumeric = true;
            } else {
                return Mode::BYTE();
            }
        }

        if ($hasalphanumeric) {
            return Mode::ALPHANUMERIC();
        } else if ($hasnumeric) {
            return Mode::NUMERIC();
        }

        return Mode::BYTE();
    }

    /**
     * Calculates the mask penalty for a matrix.
     */
    private static function calculate_mask_penalty(ByteMatrix $matrix): int {
        return (
            MaskUtil::apply_mask_penalty_rule_1($matrix)
            + MaskUtil::apply_mask_penalty_rule_2($matrix)
            + MaskUtil::apply_mask_penalty_rule_3($matrix)
            + MaskUtil::apply_mask_penalty_rule_4($matrix)
        );
    }

    /**
     * Checks if content only consists of double-byte kanji characters.
     */
    private static function is_only_double_byte_kanji(string $content): bool {
        $bytes = @iconv('utf-8', 'SHIFT-JIS', $content);

        if (false === $bytes) {
            return false;
        }

        $length = strlen($bytes);

        if (0 !== $length % 2) {
            return false;
        }

        for ($i = 0; $i < $length; $i += 2) {
            $byte = ord($bytes[$i]) & 0xff;

            if (($byte < 0x81 || $byte > 0x9f) && $byte < 0xe0 || $byte > 0xeb) {
                return false;
            }
        }

        return true;
    }

    /**
     * Chooses the best mask pattern for a matrix.
     */
    private static function choose_mask_pattern(
        BitArray $bits,
        ErrorCorrectionLevel $eclevel,
        Version $version,
        ByteMatrix $matrix
    ): int {
        $minpenalty = PHP_INT_MAX;
        $bestmaskpattern = -1;

        for ($maskpattern = 0; $maskpattern < QrCode::NUM_MASK_PATTERNS; ++$maskpattern) {
            MatrixUtil::buildmatrix($bits, $eclevel, $version, $maskpattern, $matrix);
            $penalty = self::calculate_mask_penalty($matrix);

            if ($penalty < $minpenalty) {
                $minpenalty = $penalty;
                $bestmaskpattern = $maskpattern;
            }
        }

        return $bestmaskpattern;
    }

    /**
     * Chooses the best version for the input.
     *
     * @throws WriterException if data is too big
     */
    private static function choose_version(int $numinputbits, ErrorCorrectionLevel $eclevel): Version {
        for ($versionnum = 1; $versionnum <= 40; ++$versionnum) {
            $version = Version::get_version_for_number($versionnum);
            $numbytes = $version->get_total_codewords();

            $ecblocks = $version->get_ecblocks_for_level($eclevel);
            $numecbytes = $ecblocks->get_total_ec_codewords();

            $numdatabytes = $numbytes - $numecbytes;
            $totalinputbytes = intdiv($numinputbits + 8, 8);

            if ($numdatabytes >= $totalinputbytes) {
                return $version;
            }
        }

        throw new WriterException('Data too big');
    }

    /**
     * Terminates the bits in a bit array.
     *
     * @throws WriterException if data bits cannot fit in the QR code
     * @throws WriterException if bits size does not equal the capacity
     */
    private static function terminate_bits(int $numdatabytes, BitArray $bits): void {
        $capacity = $numdatabytes << 3;

        if ($bits->get_size() > $capacity) {
            throw new WriterException('Data bits cannot fit in the QR code');
        }

        for ($i = 0; $i < 4 && $bits->get_size() < $capacity; ++$i) {
            $bits->append_bit(false);
        }

        $numbitsinlastbyte = $bits->get_size() & 0x7;

        if ($numbitsinlastbyte > 0) {
            for ($i = $numbitsinlastbyte; $i < 8; ++$i) {
                $bits->append_bit(false);
            }
        }

        $numpaddingbytes = $numdatabytes - $bits->get_size_in_bytes();

        for ($i = 0; $i < $numpaddingbytes; ++$i) {
            $bits->append_bits(0 === ($i & 0x1) ? 0xec : 0x11, 8);
        }

        if ($bits->get_size() !== $capacity) {
            throw new WriterException('Bits size does not equal capacity');
        }
    }

    /**
     * Gets number of data- and EC bytes for a block ID.
     *
     * @return int[]
     * @throws WriterException if block ID is too large
     * @throws WriterException if EC bytes mismatch
     * @throws WriterException if RS blocks mismatch
     * @throws WriterException if total bytes mismatch
     */
    private static function get_num_data_bytes_and_num_ec_bytes_for_block_id(
        int $numtotalbytes,
        int $numdatabytes,
        int $numrsblocks,
        int $blockid
    ): array {
        if ($blockid >= $numrsblocks) {
            throw new WriterException('Block ID too large');
        }

        $numrsblocksingroup2 = $numtotalbytes % $numrsblocks;
        $numrsblocksingroup1 = $numrsblocks - $numrsblocksingroup2;
        $numtotalbytesingroup1 = intdiv($numtotalbytes, $numrsblocks);
        $numtotalbytesingroup2 = $numtotalbytesingroup1 + 1;
        $numdatabytesingroup1 = intdiv($numdatabytes, $numrsblocks);
        $numdatabytesingroup2 = $numdatabytesingroup1 + 1;
        $numecbytesingroup1 = $numtotalbytesingroup1 - $numdatabytesingroup1;
        $numecbytesingroup2 = $numtotalbytesingroup2 - $numdatabytesingroup2;

        if ($numecbytesingroup1 !== $numecbytesingroup2) {
            throw new WriterException('EC bytes mismatch');
        }

        if ($numrsblocks !== $numrsblocksingroup1 + $numrsblocksingroup2) {
            throw new WriterException('RS blocks mismatch');
        }

        if (
            $numtotalbytes !==
            (($numdatabytesingroup1 + $numecbytesingroup1) * $numrsblocksingroup1)
            + (($numdatabytesingroup2 + $numecbytesingroup2) * $numrsblocksingroup2)
        ) {
            throw new WriterException('Total bytes mismatch');
        }

        if ($blockid < $numrsblocksingroup1) {
            return [$numdatabytesingroup1, $numecbytesingroup1];
        } else {
            return [$numdatabytesingroup2, $numecbytesingroup2];
        }
    }

    /**
     * Interleaves data with EC bytes.
     *
     * @throws WriterException if number of bits and data bytes does not match
     * @throws WriterException if data bytes does not match offset
     * @throws WriterException if an interleaving error occurs
     */
    private static function interleave_with_ec_bytes(
        BitArray $bits,
        int $numtotalbytes,
        int $numdatabytes,
        int $numrsblocks
    ): BitArray {
        if ($bits->get_size_in_bytes() !== $numdatabytes) {
            throw new WriterException('Number of bits and data bytes does not match');
        }

        $databytesoffset = 0;
        $maxnumdatabytes = 0;
        $maxnumecbytes   = 0;

        $blocks = new SplFixedArray($numrsblocks);

        for ($i = 0; $i < $numrsblocks; ++$i) {
            [$numdatabytesinblock, $numecbytesinblock] = self::get_num_data_bytes_and_num_ec_bytes_for_block_id(
                $numtotalbytes,
                $numdatabytes,
                $numrsblocks,
                $i
            );

            $size = $numdatabytesinblock;
            $databytes = $bits->to_bytes(8 * $databytesoffset, $size);
            $ecbytes = self::generate_ec_bytes($databytes, $numecbytesinblock);
            $blocks[$i] = new BlockPair($databytes, $ecbytes);

            $maxnumdatabytes = max($maxnumdatabytes, $size);
            $maxnumecbytes = max($maxnumecbytes, count($ecbytes));
            $databytesoffset += $numdatabytesinblock;
        }

        if ($numdatabytes !== $databytesoffset) {
            throw new WriterException('Data bytes does not match offset');
        }

        $result = new BitArray();

        for ($i = 0; $i < $maxnumdatabytes; ++$i) {
            foreach ($blocks as $block) {
                $databytes = $block->getDataBytes();

                if ($i < count($databytes)) {
                    $result->append_bits($databytes[$i], 8);
                }
            }
        }

        for ($i = 0; $i < $maxnumecbytes; ++$i) {
            foreach ($blocks as $block) {
                $ecbytes = $block->getErrorCorrectionBytes();

                if ($i < count($ecbytes)) {
                    $result->append_bits($ecbytes[$i], 8);
                }
            }
        }

        if ($numtotalbytes !== $result->get_size_in_bytes()) {
            throw new WriterException(
                'Interleaving error: ' . $numtotalbytes . ' and ' . $result->get_size_in_bytes() . ' differ'
            );
        }

        return $result;
    }

    /**
     * Generates EC bytes for given data.
     *
     * @param  SplFixedArray<int> $databytes
     * @return SplFixedArray<int>
     */
    private static function generate_ec_bytes(SplFixedArray $databytes, int $numecbytesinblock): SplFixedArray {
        $numdatabytes = count($databytes);
        $toencode = new SplFixedArray($numdatabytes + $numecbytesinblock);

        for ($i = 0; $i < $numdatabytes; $i++) {
            $toencode[$i] = $databytes[$i] & 0xff;
        }

        $ecbytes = new SplFixedArray($numecbytesinblock);
        $codec = self::get_codec($numdatabytes, $numecbytesinblock);
        $codec->encode($toencode, $ecbytes);

        return $ecbytes;
    }

    /**
     * Gets an RS codec and caches it.
     */
    private static function get_codec(int $numdatabytes, int $numecbytesinblock): ReedSolomonCodec {
        $cacheid = $numdatabytes . '-' . $numecbytesinblock;

        if (isset(self::$codecs[$cacheid])) {
            return self::$codecs[$cacheid];
        }

        return self::$codecs[$cacheid] = new ReedSolomonCodec(
            8,
            0x11d,
            0,
            1,
            $numecbytesinblock,
            255 - $numdatabytes - $numecbytesinblock
        );
    }

    /**
     * Appends mode information to a bit array.
     */
    private static function append_mode_info(Mode $mode, BitArray $bits): void {
        $bits->append_bits($mode->get_bits(), 4);
    }

    /**
     * Appends length information to a bit array.
     *
     * @throws WriterException if num letters is bigger than expected
     */
    private static function append_length_info(int $numletters, Version $version, Mode $mode, BitArray $bits): void {
        $numbits = $mode->get_character_count_bits($version);

        if ($numletters >= (1 << $numbits)) {
            throw new WriterException($numletters . ' is bigger than ' . ((1 << $numbits) - 1));
        }

        $bits->append_bits($numletters, $numbits);
    }

    /**
     * Appends bytes to a bit array in a specific mode.
     *
     * @throws WriterException if an invalid mode was supplied
     */
    private static function append_bytes(string $content, Mode $mode, BitArray $bits, string $encoding): void {
        switch ($mode) {
            case Mode::NUMERIC():
                self::append_numeric_bytes($content, $bits);
                break;

            case Mode::ALPHANUMERIC():
                self::append_alphanumeric_bytes($content, $bits);
                break;

            case Mode::BYTE():
                self::append_8_bit_bytes($content, $bits, $encoding);
                break;

            case Mode::KANJI():
                self::append_kanji_bytes($content, $bits);
                break;

            default:
                throw new WriterException('Invalid mode: ' . $mode);
        }
    }

    /**
     * Appends numeric bytes to a bit array.
     */
    private static function append_numeric_bytes(string $content, BitArray $bits): void {
        $length = strlen($content);
        $i = 0;

        while ($i < $length) {
            $num1 = (int) $content[$i];

            if ($i + 2 < $length) {
                // Encode three numeric letters in ten bits.
                $num2 = (int) $content[$i + 1];
                $num3 = (int) $content[$i + 2];
                $bits->append_bits($num1 * 100 + $num2 * 10 + $num3, 10);
                $i += 3;
            } else if ($i + 1 < $length) {
                // Encode two numeric letters in seven bits.
                $num2 = (int) $content[$i + 1];
                $bits->append_bits($num1 * 10 + $num2, 7);
                $i += 2;
            } else {
                // Encode one numeric letter in four bits.
                $bits->append_bits($num1, 4);
                ++$i;
            }
        }
    }

    /**
     * Appends alpha-numeric bytes to a bit array.
     *
     * @throws WriterException if an invalid alphanumeric code was found
     */
    private static function append_alphanumeric_bytes(string $content, BitArray $bits): void {
        $length = strlen($content);
        $i = 0;

        while ($i < $length) {
            $code1 = self::get_alphanumeric_code(ord($content[$i]));

            if (-1 === $code1) {
                throw new WriterException('Invalid alphanumeric code');
            }

            if ($i + 1 < $length) {
                $code2 = self::get_alphanumeric_code(ord($content[$i + 1]));

                if (-1 === $code2) {
                    throw new WriterException('Invalid alphanumeric code');
                }

                // Encode two alphanumeric letters in 11 bits.
                $bits->append_bits($code1 * 45 + $code2, 11);
                $i += 2;
            } else {
                // Encode one alphanumeric letter in six bits.
                $bits->append_bits($code1, 6);
                ++$i;
            }
        }
    }

    /**
     * Appends regular 8-bit bytes to a bit array.
     *
     * @throws WriterException if content cannot be encoded to target encoding
     */
    private static function append_8_bit_bytes(string $content, BitArray $bits, string $encoding): void {
        $bytes = @iconv('utf-8', $encoding, $content);

        if (false === $bytes) {
            throw new WriterException('Could not encode content to ' . $encoding);
        }

        $length = strlen($bytes);

        for ($i = 0; $i < $length; $i++) {
            $bits->append_bits(ord($bytes[$i]), 8);
        }
    }

    /**
     * Appends KANJI bytes to a bit array.
     *
     * @throws WriterException if content does not seem to be encoded in SHIFT-JIS
     * @throws WriterException if an invalid byte sequence occurs
     */
    private static function append_kanji_bytes(string $content, BitArray $bits): void {
        $bytes = @iconv('utf-8', 'SHIFT-JIS', $content);

        if (false === $bytes) {
            throw new WriterException('Content could not be converted to SHIFT-JIS');
        }

        if (strlen($bytes) % 2 > 0) {
            // We just do a simple length check here. The for loop will check
            // individual characters.
            throw new WriterException('Content does not seem to be encoded in SHIFT-JIS');
        }

        $length = strlen($bytes);

        for ($i = 0; $i < $length; $i += 2) {
            $byte1 = ord($bytes[$i]) & 0xff;
            $byte2 = ord($bytes[$i + 1]) & 0xff;
            $code = ($byte1 << 8) | $byte2;

            if ($code >= 0x8140 && $code <= 0x9ffc) {
                $subtracted = $code - 0x8140;
            } else if ($code >= 0xe040 && $code <= 0xebbf) {
                $subtracted = $code - 0xc140;
            } else {
                throw new WriterException('Invalid byte sequence');
            }

            $encoded = (($subtracted >> 8) * 0xc0) + ($subtracted & 0xff);

            $bits->append_bits($encoded, 13);
        }
    }

    /**
     * Appends ECI information to a bit array.
     */
    private static function append_eci(CharacterSetEci $eci, BitArray $bits): void {
        $mode = Mode::ECI();
        $bits->append_bits($mode->getbits(), 4);
        $bits->append_bits($eci->getvalue(), 8);
    }
}
