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

namespace DASPRiD\Enum;

use DASPRiD\Enum\Exception\CloneNotSupportedException;
use DASPRiD\Enum\Exception\SerializeNotSupportedException;
use DASPRiD\Enum\Exception\UnserializeNotSupportedException;

final class NullValue
{
    /**
     * @var self
     */
    private static $instance;

    private function __construct() {
    }

    public static function instance(): self {
        return self::$instance ?: self::$instance = new self();
    }

    /**
     * Forbid cloning enums.
     *
     * @throws CloneNotSupportedException
     */
    final public function __clone() {
        throw new CloneNotSupportedException();
    }

    /**
     * Forbid serializing enums.
     *
     * @throws SerializeNotSupportedException
     */
    final public function __sleep(): array {
        throw new SerializeNotSupportedException();
    }

    /**
     * Forbid serializing enums.
     *
     * @throws SerializeNotSupportedException
     */
    final public function __serialize(): array {
        throw new SerializeNotSupportedException();
    }

    /**
     * Forbid unserializing enums.
     *
     * @throws UnserializeNotSupportedException
     */
    final public function __wakeup(): void {
        throw new UnserializeNotSupportedException();
    }

    /**
     * Forbid unserializing enums.
     *
     * @throws UnserializeNotSupportedException
     */
    final public function __unserialize($arg): void {
        throw new UnserializeNotSupportedException();
    }
}
