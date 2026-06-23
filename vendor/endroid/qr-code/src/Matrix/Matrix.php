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

namespace Endroid\QrCode\Matrix;

use Endroid\QrCode\Exception\BlockSizeTooSmallException;
use Endroid\QrCode\RoundBlockSizeMode;

final readonly class Matrix implements MatrixInterface
{
    private float $blockSize;
    private int $innerSize;
    private int $outerSize;
    private int $marginLeft;
    private int $marginRight;

    /** @param array<array<int>> $blockValues */
    public function __construct(
        private array $blockValues,
        int $size,
        int $margin,
        RoundBlockSizeMode $roundBlockSizeMode,
    ) {
        $blockSize = $size / $this->getBlockCount();
        $innerSize = $size;
        $outerSize = $size + 2 * $margin;

        switch ($roundBlockSizeMode) {
            case RoundBlockSizeMode::Enlarge:
                $blockSize = intval(ceil($blockSize));
                $innerSize = intval($blockSize * $this->getBlockCount());
                $outerSize = $innerSize + 2 * $margin;
                break;
            case RoundBlockSizeMode::Shrink:
                $blockSize = intval(floor($blockSize));
                $innerSize = intval($blockSize * $this->getBlockCount());
                $outerSize = $innerSize + 2 * $margin;
                break;
            case RoundBlockSizeMode::Margin:
                $blockSize = intval(floor($blockSize));
                $innerSize = intval($blockSize * $this->getBlockCount());
                break;
        }

        if ($blockSize < 1) {
            throw new BlockSizeTooSmallException('Too much data: increase image dimensions or lower error correction level');
        }

        $this->blockSize = $blockSize;
        $this->innerSize = $innerSize;
        $this->outerSize = $outerSize;
        $this->marginLeft = intval(($this->outerSize - $this->innerSize) / 2);
        $this->marginRight = $this->outerSize - $this->innerSize - $this->marginLeft;
    }

    public function getBlockValue(int $rowIndex, int $columnIndex): int {
        return $this->blockValues[$rowIndex][$columnIndex];
    }

    public function getBlockCount(): int {
        return count($this->blockValues[0]);
    }

    public function getBlockSize(): float {
        return $this->blockSize;
    }

    public function getInnerSize(): int {
        return $this->innerSize;
    }

    public function getOuterSize(): int {
        return $this->outerSize;
    }

    public function getMarginLeft(): int {
        return $this->marginLeft;
    }

    public function getMarginRight(): int {
        return $this->marginRight;
    }
}
