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

namespace BaconQrCode\Renderer;

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Exception\InvalidArgumentException;

/**
 * Plain text renderer.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class PlainTextRenderer implements RendererInterface
{
    /**
     * UTF-8 full block (U+2588)
     */
    private const FULL_BLOCK = "\xe2\x96\x88";

    /**
     * UTF-8 upper half block (U+2580)
     */
    private const UPPER_HALF_BLOCK = "\xe2\x96\x80";

    /**
     * UTF-8 lower half block (U+2584)
     */
    private const LOWER_HALF_BLOCK = "\xe2\x96\x84";

    /**
     * UTF-8 no-break space (U+00A0)
     */
    private const EMPTY_BLOCK = "\xc2\xa0";

    /**
     * Constructor.
     *
     * @param int $margin
     */
    public function __construct(
        /**
         * @var int
         */
        private readonly int $margin = 2
    ) {
    }

    /**
     * Renders the QR code to a plain text representation.
     *
     * @throws InvalidArgumentException if matrix width doesn't match height
     */
    public function render(QrCode $qrcode): string {
        $matrix = $qrcode->get_matrix();
        $matrixsize = $matrix->get_width();

        if ($matrixsize !== $matrix->get_height()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        $rows = $matrix->get_array()->toArray();

        if (0 !== $matrixsize % 2) {
            $rows[] = array_fill(0, $matrixsize, 0);
        }

        $horizontalmargin = str_repeat(self::EMPTY_BLOCK, $this->margin);
        $result = str_repeat("\n", (int) ceil($this->margin / 2));

        for ($i = 0; $i < $matrixsize; $i += 2) {
            $result .= $horizontalmargin;

            $upperrow = $rows[$i];
            $lowerrow = $rows[$i + 1];

            for ($j = 0; $j < $matrixsize; ++$j) {
                $upperbit = $upperrow[$j];
                $lowerbit = $lowerrow[$j];

                if ($upperbit) {
                    $result .= $lowerbit ? self::FULL_BLOCK : self::UPPER_HALF_BLOCK;
                } else {
                    $result .= $lowerbit ? self::LOWER_HALF_BLOCK : self::EMPTY_BLOCK;
                }
            }

            $result .= $horizontalmargin . "\n";
        }

        $result .= str_repeat("\n", (int) ceil($this->margin / 2));

        return $result;
    }
}
