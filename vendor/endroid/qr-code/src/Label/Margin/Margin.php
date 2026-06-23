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

namespace Endroid\QrCode\Label\Margin;

final readonly class Margin implements MarginInterface
{
    public function __construct(
        private int $top,
        private int $right,
        private int $bottom,
        private int $left,
    ) {
    }

    public function getTop(): int {
        return $this->top;
    }

    public function getRight(): int {
        return $this->right;
    }

    public function getBottom(): int {
        return $this->bottom;
    }

    public function getLeft(): int {
        return $this->left;
    }

    /** @return array<string, int> */
    public function toArray(): array {
        return [
            'top' => $this->top,
            'right' => $this->right,
            'bottom' => $this->bottom,
            'left' => $this->left,
        ];
    }
}
