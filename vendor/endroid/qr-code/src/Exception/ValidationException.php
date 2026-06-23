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

final class ValidationException extends \Exception
{
    public static function createForUnsupportedWriter(string $writerClass): self {
        return new self(sprintf('Unable to validate the result: "%s" does not support validation', $writerClass));
    }

    public static function createForMissingPackage(string $packageName): self {
        return new self(sprintf('Please install "%s" or disable image validation', $packageName));
    }

    public static function createForInvalidData(string $expectedData, string $actualData): self {
        return new self('The validation reader read "' . $actualData . '" instead of "' . $expectedData . '". Adjust your parameters to increase readability or disable validation.');
    }
}
