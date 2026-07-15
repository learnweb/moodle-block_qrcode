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

namespace Endroid\QrCode\Exception;

/**
 * Exception thrown when validation fails.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ValidationException extends \Exception
{
    /**
     * Creates a new ValidationException for an unsupported writer.
     *
     * @param string $writerclass
     * @return self
     */
    public static function create_for_unsupported_writer(string $writerclass): self {
        return new self(
            sprintf('Unable to validate the result: "%s" does not support validation', $writerclass)
        );
    }

    /**
     * Creates a new ValidationException for a missing package.
     *
     * @param string $packagename
     * @return self
     */
    public static function create_for_missing_package(string $packagename): self {
        return new self(sprintf('Please install "%s" or disable image validation', $packagename));
    }

    /**
     * Creates a new ValidationException for invalid data.
     *
     * @param string $expecteddata
     * @param string $actualdata
     * @return self
     */
    public static function create_for_invalid_data(string $expecteddata, string $actualdata): self {
        return new self('The validation reader read "' . $actualdata . '" instead of "' . $expecteddata .
                '". Adjust your parameters to increase readability or disable validation.');
    }
}
