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

namespace BaconQrCode\Common;

use BaconQrCode\Exception\InvalidArgumentException;
use DASPRiD\Enum\AbstractEnum;

/**
 * Encapsulates a Character Set ECI, according to "Extended Channel Interpretations" 5.3.1.1 of ISO 18004.
 *
 * @copyright 2024 J. Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class CharacterSetEci extends AbstractEnum
{
    /**
     * CP437 character set ECI.
     */
    protected const CP437 = [[0, 2]];
    /**
     * ISO-8859-1 character set ECI.
     */
    protected const ISO8859_1 = [[1, 3], 'ISO-8859-1'];
    /**
     * ISO-8859-2 character set ECI.
     */
    protected const ISO8859_2 = [[4], 'ISO-8859-2'];
    /**
     * ISO-8859-3 character set ECI.
     */
    protected const ISO8859_3 = [[5], 'ISO-8859-3'];
    /**
     * ISO-8859-4 character set ECI.
     */
    protected const ISO8859_4 = [[6], 'ISO-8859-4'];
    /**
     * ISO-8859-5 character set ECI.
     */
    protected const ISO8859_5 = [[7], 'ISO-8859-5'];
    /**
     * ISO-8859-6 character set ECI.
     */
    protected const ISO8859_6 = [[8], 'ISO-8859-6'];
    /**
     * ISO-8859-7 character set ECI.
     */
    protected const ISO8859_7 = [[9], 'ISO-8859-7'];
    /**
     * ISO-8859-8 character set ECI.
     */
    protected const ISO8859_8 = [[10], 'ISO-8859-8'];
    /**
     * ISO-8859-9 character set ECI.
     */
    protected const ISO8859_9 = [[11], 'ISO-8859-9'];
    /**
     * ISO-8859-10 character set ECI.
     */
    protected const ISO8859_10 = [[12], 'ISO-8859-10'];
    /**
     * ISO-8859-11 character set ECI.
     */
    protected const ISO8859_11 = [[13], 'ISO-8859-11'];
    /**
     * ISO-8859-12 character set ECI.
     */
    protected const ISO8859_12 = [[14], 'ISO-8859-12'];
    /**
     * ISO-8859-13 character set ECI.
     */
    protected const ISO8859_13 = [[15], 'ISO-8859-13'];
    /**
     * ISO-8859-14 character set ECI.
     */
    protected const ISO8859_14 = [[16], 'ISO-8859-14'];
    /**
     * ISO-8859-15 character set ECI.
     */
    protected const ISO8859_15 = [[17], 'ISO-8859-15'];
    /**
     * ISO-8859-16 character set ECI.
     */
    protected const ISO8859_16 = [[18], 'ISO-8859-16'];
    /**
     * Shift JIS character set ECI.
     */
    protected const SJIS = [[20], 'Shift_JIS'];
    /**
     * Windows-1250 character set ECI.
     */
    protected const CP1250 = [[21], 'windows-1250'];
    /**
     * Windows-1251 character set ECI.
     */
    protected const CP1251 = [[22], 'windows-1251'];
    /**
     * Windows-1252 character set ECI.
     */
    protected const CP1252 = [[23], 'windows-1252'];
    /**
     * Windows-1256 character set ECI.
     */
    protected const CP1256 = [[24], 'windows-1256'];
    /**
     * UTF-16BE / UnicodeBig character set ECI.
     */
    protected const UNICODE_BIG_UNMARKED = [[25], 'UTF-16BE', 'UnicodeBig'];
    /**
     * UTF-8 character set ECI.
     */
    protected const UTF8 = [[26], 'UTF-8'];
    /**
     * ASCII character set ECI.
     */
    protected const ASCII = [[27, 170], 'US-ASCII'];
    /**
     * Big5 character set ECI.
     */
    protected const BIG5 = [[28]];
    /**
     * GB18030 character set ECI.
     */
    protected const GB18030 = [[29], 'GB2312', 'EUC_CN', 'GBK'];
    /**
     * EUC-KR character set ECI.
     */
    protected const EUC_KR = [[30], 'EUC-KR'];

    /**
     * @var string[]
     */
    private array $otherencodingnames;

    /**
     * @var array<int, self>|null
     */
    private static ?array $valuetoeci;

    /**
     * @var array<string, self>|null
     */
    private static ?array $nametoeci = null;

    /**
     * @var array
     */
    private readonly array $values;

    /**
     * Constructs a CharacterSetEci with the given values and other encoding names.
     * @param int[] $values
     */
    public function __construct(array $values, string ...$otherencodingnames) {
        $this->values = $values;
        $this->otherencodingnames = $otherencodingnames;
    }

    /**
     *
     * Returns the primary value.
     */
    public function getvalue(): int {
        return $this->values[0];
    }

    /**
     * Gets character set ECI by value.
     *
     * Returns the representing ECI of a given value, or null if it is legal but unsupported.
     *
     * @throws InvalidArgumentException if value is not between 0 and 900
     */
    public static function getcharactersetecibyvalue(int $value): ?self {
        if ($value < 0 || $value >= 900) {
            throw new InvalidArgumentException('Value must be between 0 and 900');
        }

        $valuetoeci = self::valuetoeci();

        if (! array_key_exists($value, $valuetoeci)) {
            return null;
        }

        return $valuetoeci[$value];
    }

    /**
     * Returns character set ECI by name.
     *
     * Returns the representing ECI of a given name, or null if it is legal but unsupported
     */
    public static function getcharactersetecibyname(string $name): ?self {
        $nametoeci = self::nametoeci();
        $name = strtolower($name);

        if (! array_key_exists($name, $nametoeci)) {
            return null;
        }

        return $nametoeci[$name];
    }

    /**
     * Returns a mapping of values to CharacterSetEci instances.
     * @return array|CharacterSetEci[]
     */
    private static function valuetoeci(): array {
        if (null !== self::$valuetoeci) {
            return self::$valuetoeci;
        }

        self::$valuetoeci = [];

        foreach (self::values() as $eci) {
            foreach ($eci->values as $value) {
                self::$valuetoeci[$value] = $eci;
            }
        }

        return self::$valuetoeci;
    }

    /**
     * Returns a mapping of names to CharacterSetEci instances.
     * @return array|CharacterSetEci[]
     */
    private static function nametoeci(): array {
        if (null !== self::$nametoeci) {
            return self::$nametoeci;
        }

        self::$nametoeci = [];

        foreach (self::values() as $eci) {
            self::$nametoeci[strtolower($eci->name())] = $eci;

            foreach ($eci->otherencodingnames as $name) {
                self::$nametoeci[strtolower($name)] = $eci;
            }
        }

        return self::$nametoeci;
    }
}
