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

namespace BaconQrCode\Common;

/**
 * Encapsulates a set of error-correction blocks in one symbol version.
 *
 * Most versions will use blocks of differing sizes within one version, so, this encapsulates the parameters for each
 * set of blocks. It also holds the number of error-correction codewords per block since it will be the same across all
 * blocks within one version.
 *
 * @copyright 2024 J. Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EcBlocks
{
    /**
     * List of EC blocks.
     *
     * @var EcBlock[]
     */
    private array $ecblocks;

    /**
     * The number of error-correction codewords per block.
     *
     * @var int
     */
    private readonly int $eccodewordsperblock;

    /**
     * Constructor.
     *
     * @param int $eccodewordsperblock
     * @param EcBlock ...$ecblocks
     */
    public function __construct(int $eccodewordsperblock, EcBlock ...$ecblocks) {
        $this->ecblocks = $ecblocks;
    }

    /**
     * Returns the number of EC codewords per block.
     */
    public function geteccodewordsperblock(): int {
        return $this->eccodewordsperblock;
    }

    /**
     * Returns the total number of EC block appearances.
     */
    public function getnumblocks(): int {
        $total = 0;

        foreach ($this->ecblocks as $ecblock) {
            $total += $ecblock->getcount();
        }

        return $total;
    }

    /**
     * Returns the total count of EC codewords.
     */
    public function gettotaleccodewords(): int {
        return $this->eccodewordsperblock * $this->getnumblocks();
    }

    /**
     * Returns the EC blocks included in this collection.
     *
     * @return EcBlock[]
     */
    public function getecblocks(): array {
        return $this->ecblocks;
    }
}
