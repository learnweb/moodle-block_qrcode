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

namespace BaconQrCode\Renderer\Path;

/**
 * Represents a close path operation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Close implements OperationInterface
{
    /**
     * @var Close|null
     */
    private static ?Close $instance = null;

    /**
     * Constructor.
     */
    private function __construct() {
    }

    /**
     * Returns the close operation instance.
     *
     * @return self
     */
    public static function instance(): self {
        return self::$instance ?: self::$instance = new self();
    }

    /**
     * Returns this close operation unchanged.
     *
     * @return self
     */
    public function translate(float $x, float $y): OperationInterface {
        return $this;
    }

    /**
     * Returns this close operation unchanged.
     *
     * @return self
     */
    public function rotate(int $degrees): OperationInterface {
        return $this;
    }
}
