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

namespace Endroid\QrCode\Logo;

final readonly class Logo implements LogoInterface
{
    public function __construct(
        private string $path,
        private ?int $resizeToWidth = null,
        private ?int $resizeToHeight = null,
        private bool $punchoutBackground = false,
    ) {
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getResizeToWidth(): ?int {
        return $this->resizeToWidth;
    }

    public function getResizeToHeight(): ?int {
        return $this->resizeToHeight;
    }

    public function getPunchoutBackground(): bool {
        return $this->punchoutBackground;
    }
}
