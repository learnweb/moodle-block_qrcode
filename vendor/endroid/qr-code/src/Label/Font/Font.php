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

namespace Endroid\QrCode\Label\Font;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a font used for labels in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class Font implements FontInterface
{
    /**
     * Constructor.
     *
     * @param string $path
     * @param int $size
     * @throws \Exception
     */
    public function __construct(
        /**
         * @var string
         */
        private string $path,
        /**
         * @var int
         */
        private int $size = 16,
    ) {
        $this->assert_valid_path($path);
    }

    /**
     * Asserts that the given font path is valid (i.e., the file exists).
     *
     * @param string $path
     * @return void
     * @throws \Exception
     */
    private function assert_valid_path(string $path): void {
        if (!file_exists($path)) {
            throw new \Exception(sprintf('Invalid font path "%s"', $path));
        }
    }

    /**
     * Returns the path to the font file.
     *
     * @return string
     */
    public function get_path(): string {
        return $this->path;
    }

    /**
     * Returns the size of the font.
     *
     * @return int
     */
    public function get_size(): int {
        return $this->size;
    }
}
