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

namespace Endroid\QrCode\Encoding;

final readonly class Encoding implements EncodingInterface
{
    public function __construct(
        private string $value,
    ) {
        if ('UTF-8' !== $value) {
            if (!function_exists('mb_list_encodings')) {
                throw new \Exception('Unable to validate encoding: make sure the mbstring extension is installed and enabled');
            }

            if (!in_array($value, mb_list_encodings())) {
                throw new \Exception(sprintf('Invalid encoding "%s": choose one of ' . implode(', ', mb_list_encodings()), $value));
            }
        }
    }

    public function __toString(): string {
        return $this->value;
    }
}
