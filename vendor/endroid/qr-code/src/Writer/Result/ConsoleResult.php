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

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Matrix\MatrixInterface;

/**
 * Represents the result of writing a QR code in console format.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ConsoleResult extends AbstractResult
{
    /**
     * Two-block character map.
     */
    private const TWO_BLOCKS = [
        0 => ' ',
        1 => "\xe2\x96\x80",
        2 => "\xe2\x96\x84",
        3 => "\xe2\x96\x88",
    ];
    /**
     * @var string
     */
    private string $colorescapecode;

    /**
     * Constructor.
     *
     * @param MatrixInterface $matrix
     * @param ColorInterface $foreground
     * @param ColorInterface $background
     */
    public function __construct(
        MatrixInterface $matrix,
        ColorInterface $foreground,
        ColorInterface $background,
    ) {
        parent::__construct($matrix);

        $this->colorescapecode = sprintf(
            "\e[38;2;%d;%d;%dm\e[48;2;%d;%d;%dm",
            $foreground->get_red(),
            $foreground->get_green(),
            $foreground->get_blue(),
            $background->get_red(),
            $background->get_green(),
            $background->get_blue()
        );
    }

    /**
     * Returns the MIME type for the console representation of the QR code.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return 'text/plain';
    }

    /**
     * Returns the string representation of the QR code in console format.
     *
     * @return string
     */
    public function get_string(): string {
        $matrix = $this->get_matrix();

        $side = $matrix->get_block_count();
        $marginleft = $this->colorescapecode . self::TWO_BLOCKS[0] . self::TWO_BLOCKS[0];
        $marginright = self::TWO_BLOCKS[0] . self::TWO_BLOCKS[0] . "\e[0m" . PHP_EOL;
        $marginvertical = $marginleft . str_repeat(self::TWO_BLOCKS[0], $side) . $marginright;

        $qrcodestring = $marginvertical;
        for ($rowindex = 0; $rowindex < $side; $rowindex += 2) {
            $qrcodestring .= $marginleft;
            for ($columnindex = 0; $columnindex < $side; ++$columnindex) {
                $combined = $matrix->get_block_value($rowindex, $columnindex);
                if ($rowindex + 1 < $side) {
                    $combined |= $matrix->get_block_value($rowindex + 1, $columnindex) << 1;
                }
                $qrcodestring .= self::TWO_BLOCKS[$combined];
            }
            $qrcodestring .= $marginright;
        }
        $qrcodestring .= $marginvertical;

        return $qrcodestring;
    }
}
