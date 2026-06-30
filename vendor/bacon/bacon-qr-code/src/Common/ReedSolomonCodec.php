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

use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Exception\RuntimeException;
use SplFixedArray;

/**
 * Reed-Solomon codec for 8-bit characters.
 *
 * Based on libfec by Phil Karn, KA9Q.
 *
 * @copyright  2017 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ReedSolomonCodec
{
    /**
     * Symbol size in bits.
     *
     * @var int
     */
    private int $symbolsize;

    /**
     * Block size in symbols.
     *
     * @var int
     */
    private int $blocksize;

    /**
     * First root of RS code generator polynomial, index form.
     *
     * @var int
     */
    private int $firstroot;

    /**
     * Primitive element to generate polynomial roots, index form.
     *
     * @var int
     */
    private int $primitive;

    /**
     * Prim-th root of 1, index form.
     *
     * @var int
     */
    private int $iprimitive;

    /**
     * RS code generator polynomial degree (number of roots).
     *
     * @var int
     */
    private int $numroots;

    /**
     * Padding bytes at front of shortened block.
     *
     * @var int
     */
    private int $padding;

    /**
     * Log lookup table.
     *
     * @var SplFixedArray
     */
    private SplFixedArray $alphato;

    /**
     * Anti-Log lookup table.
     *
     * @var SplFixedArray
     */
    private SplFixedArray $indexof;

    /**
     * Generator polynomial.
     *
     * @var SplFixedArray
     */
    private SplFixedArray $generatorpoly;

    /**
     * @throws InvalidArgumentException if symbol size ist not between 0 and 8
     * @throws InvalidArgumentException if first root is invalid
     * @throws InvalidArgumentException if num roots is invalid
     * @throws InvalidArgumentException if padding is invalid
     * @throws RuntimeException if field generator polynomial is not primitive
     */
    public function __construct(
        int $symbolsize,
        int $gfpoly,
        int $firstroot,
        int $primitive,
        int $numroots,
        int $padding
    ) {
        if ($symbolsize < 0 || $symbolsize > 8) {
            throw new InvalidArgumentException('Symbol size must be between 0 and 8');
        }

        if ($firstroot < 0 || $firstroot >= (1 << $symbolsize)) {
            throw new InvalidArgumentException('First root must be between 0 and ' . (1 << $symbolsize));
        }

        if ($numroots < 0 || $numroots >= (1 << $symbolsize)) {
            throw new InvalidArgumentException('Num roots must be between 0 and ' . (1 << $symbolsize));
        }

        if ($padding < 0 || $padding >= ((1 << $symbolsize) - 1 - $numroots)) {
            throw new InvalidArgumentException(
                'Padding must be between 0 and ' . ((1 << $symbolsize) - 1 - $numroots)
            );
        }

        $this->symbolsize = $symbolsize;
        $this->blocksize = (1 << $symbolsize) - 1;
        $this->padding = $padding;
        $this->alphato = SplFixedArray::fromArray(array_fill(0, $this->blocksize + 1, 0), false);
        $this->indexof = SplFixedArray::fromArray(array_fill(0, $this->blocksize + 1, 0), false);

        // Generate galous field lookup table
        $this->indexof[0] = $this->blocksize;
        $this->alphato[$this->blocksize] = 0;

        $sr = 1;

        for ($i = 0; $i < $this->blocksize; ++$i) {
            $this->indexof[$sr] = $i;
            $this->alphato[$i]  = $sr;

            $sr <<= 1;

            if ($sr & (1 << $symbolsize)) {
                $sr ^= $gfpoly;
            }

            $sr &= $this->blocksize;
        }

        if (1 !== $sr) {
            throw new RuntimeException('Field generator polynomial is not primitive');
        }

        // Form RS code generator polynomial from its roots.
        $this->generatorpoly = SplFixedArray::fromArray(array_fill(0, $numroots + 1, 0), false);
        $this->firstroot = $firstroot;
        $this->primitive = $primitive;
        $this->numroots = $numroots;

        // Find prim-th root of 1, used in decoding.
        for ($iPrimitive = 1; ($iPrimitive % $primitive) !== 0; $iPrimitive += $this->blocksize) {
        }

        $this->iprimitive = intdiv($iPrimitive, $primitive);

        $this->generatorpoly[0] = 1;

        for ($i = 0, $root = $firstroot * $primitive; $i < $numroots; ++$i, $root += $primitive) {
            $this->generatorpoly[$i + 1] = 1;

            for ($j = $i; $j > 0; $j--) {
                if ($this->generatorpoly[$j] !== 0) {
                    $this->generatorpoly[$j] = $this->generatorpoly[$j - 1] ^ $this->alphato[$this->modnn($this->indexof[$this->generatorpoly[$j]] + $root)];
                } else {
                    $this->generatorpoly[$j] = $this->generatorpoly[$j - 1];
                }
            }

            $this->generatorpoly[$j] = $this->alphato[$this->modnn($this->indexof[$this->generatorpoly[0]] + $root)];
        }

        // Convert generator poly to index form for quicker encoding.
        for ($i = 0; $i <= $numroots; ++$i) {
            $this->generatorpoly[$i] = $this->indexof[$this->generatorpoly[$i]];
        }
    }

    /**
     * Encodes data and writes result back into parity array.
     */
    public function encode(SplFixedArray $data, SplFixedArray $parity): void {
        for ($i = 0; $i < $this->numroots; ++$i) {
            $parity[$i] = 0;
        }

        $iterations = $this->blocksize - $this->numroots - $this->padding;

        for ($i = 0; $i < $iterations; ++$i) {
            $feedback = $this->indexof[$data[$i] ^ $parity[0]];

            if ($feedback !== $this->blocksize) {
                // Feedback term is non-zero
                $feedback = $this->modnn($this->blocksize - $this->generatorpoly[$this->numroots] + $feedback);

                for ($j = 1; $j < $this->numroots; ++$j) {
                    $parity[$j] = $parity[$j] ^ $this->alphato[$this->modnn($feedback + $this->generatorpoly[$this->numroots - $j])];
                }
            }

            for ($j = 0; $j < $this->numroots - 1; ++$j) {
                $parity[$j] = $parity[$j + 1];
            }

            if ($feedback !== $this->blocksize) {
                $parity[$this->numroots - 1] = $this->alphato[$this->modnn($feedback + $this->generatorpoly[0])];
            } else {
                $parity[$this->numroots - 1] = 0;
            }
        }
    }

    /**
     * Decodes received data.
     */
    public function decode(SplFixedArray $data, ?SplFixedArray $erasures = null): ?int {
        // This speeds up the initialization a bit.
        $numrootsplusone = SplFixedArray::fromArray(array_fill(0, $this->numroots + 1, 0), false);
        $numroots = SplFixedArray::fromArray(array_fill(0, $this->numroots, 0), false);

        $lambda = clone $numrootsplusone;
        $b = clone $numrootsplusone;
        $t = clone $numrootsplusone;
        $omega = clone $numrootsplusone;
        $root = clone $numroots;
        $loc = clone $numroots;

        $numerasures = (null !== $erasures ? count($erasures) : 0);

        // Form the Syndromes; i.e., evaluate data(x) at roots of g(x).
        $syndromes = SplFixedArray::fromArray(array_fill(0, $this->numroots, $data[0]), false);

        for ($i = 1; $i < $this->blocksize - $this->padding; ++$i) {
            for ($j = 0; $j < $this->numroots; ++$j) {
                if ($syndromes[$j] === 0) {
                    $syndromes[$j] = $data[$i];
                } else {
                    $syndromes[$j] = $data[$i] ^ $this->alphato[$this->modnn($this->indexof[$syndromes[$j]] + ($this->firstroot + $j) * $this->primitive)];
                }
            }
        }

        // Convert syndromes to index form, checking for nonzero conditions.
        $syndromeerror = 0;

        for ($i = 0; $i < $this->numroots; ++$i) {
            $syndromeerror |= $syndromes[$i];
            $syndromes[$i] = $this->indexof[$syndromes[$i]];
        }

        if (! $syndromeerror) {
            /* If syndrome is zero, data[] is a codeword and there are no errors to correct, so return data[]
            unmodified. */
            return 0;
        }

        $lambda[0] = 1;

        if ($numerasures > 0) {
            // Init lambda to be the erasure locator polynomial.
            $lambda[1] = $this->alphato[$this->modnn($this->primitive * ($this->blocksize - 1 - $erasures[0]))];

            for ($i = 1; $i < $numerasures; ++$i) {
                $u = $this->modnn($this->primitive * ($this->blocksize - 1 - $erasures[$i]));

                for ($j = $i + 1; $j > 0; --$j) {
                    $tmp = $this->indexof[$lambda[$j - 1]];

                    if ($tmp !== $this->blocksize) {
                        $lambda[$j] = $lambda[$j] ^ $this->alphato[$this->modnn($u + $tmp)];
                    }
                }
            }
        }

        for ($i = 0; $i <= $this->numroots; ++$i) {
            $b[$i] = $this->indexof[$lambda[$i]];
        }

        // Begin Berlekamp-Massey algorithm to determine error+erasure locator polynomial.
        $r  = $numerasures;
        $el = $numerasures;

        while (++$r <= $this->numroots) {
            // Compute discrepancy at the r-th step in poly form.
            $discrepancyr = 0;

            for ($i = 0; $i < $r; ++$i) {
                if ($lambda[$i] !== 0 && $syndromes[$r - $i - 1] !== $this->blocksize) {
                    $discrepancyr ^= $this->alphato[$this->modnn($this->indexof[$lambda[$i]] + $syndromes[$r - $i - 1])];
                }
            }

            $discrepancyr = $this->indexof[$discrepancyr];

            if ($discrepancyr === $this->blocksize) {
                $tmp = $b->toArray();
                array_unshift($tmp, $this->blocksize);
                array_pop($tmp);
                $b = SplFixedArray::fromArray($tmp, false);
                continue;
            }

            $t[0] = $lambda[0];

            for ($i = 0; $i < $this->numroots; ++$i) {
                if ($b[$i] !== $this->blocksize) {
                    $t[$i + 1] = $lambda[$i + 1] ^ $this->alphato[$this->modnn($discrepancyr + $b[$i])];
                } else {
                    $t[$i + 1] = $lambda[$i + 1];
                }
            }

            if (2 * $el <= $r + $numerasures - 1) {
                $el = $r + $numerasures - $el;

                for ($i = 0; $i <= $this->numroots; ++$i) {
                    $b[$i] = (
                        $lambda[$i] === 0
                        ? $this->blocksize
                        : $this->modnn($this->indexof[$lambda[$i]] - $discrepancyr + $this->blocksize)
                    );
                }
            } else {
                $tmp = $b->toArray();
                array_unshift($tmp, $this->blocksize);
                array_pop($tmp);
                $b = SplFixedArray::fromArray($tmp, false);
            }

            $lambda = clone $t;
        }

        // Convert lambda to index form and compute deg(lambda(x)).
        $deglambda = 0;

        for ($i = 0; $i <= $this->numroots; ++$i) {
            $lambda[$i] = $this->indexof[$lambda[$i]];

            if ($lambda[$i] !== $this->blocksize) {
                $deglambda = $i;
            }
        }

        // Find roots of the error+erasure locator polynomial by Chien search.
        $reg = clone $lambda;
        $reg[0] = 0;
        $count = 0;
        $i = 1;

        for ($k = $this->iprimitive - 1; $i <= $this->blocksize; ++$i, $k = $this->modnn($k + $this->iprimitive)) {
            $q = 1;

            for ($j = $deglambda; $j > 0; $j--) {
                if ($reg[$j] !== $this->blocksize) {
                    $reg[$j] = $this->modnn($reg[$j] + $j);
                    $q ^= $this->alphato[$reg[$j]];
                }
            }

            if ($q !== 0) {
                // Not a root.
                continue;
            }

            // Store root (index-form) and error location number.
            $root[$count] = $i;
            $loc[$count] = $k;

            if (++$count === $deglambda) {
                break;
            }
        }

        if ($deglambda !== $count) {
            // deg(lambda) unequal to number of roots: uncorrectable error detected.
            return null;
        }

        // Compute err+eras evaluate poly omega(x) = s(x)*lambda(x) (modulo x**numroots). In index form. Also find
        // deg(omega).
        $degomega = $deglambda - 1;

        for ($i = 0; $i <= $degomega; ++$i) {
            $tmp = 0;

            for ($j = $i; $j >= 0; --$j) {
                if ($syndromes[$i - $j] !== $this->blocksize && $lambda[$j] !== $this->blocksize) {
                    $tmp ^= $this->alphato[$this->modnn($syndromes[$i - $j] + $lambda[$j])];
                }
            }

            $omega[$i] = $this->indexof[$tmp];
        }

        /* Compute error values in poly-form. num1 = omega(inv(X(l))), num2 = inv(X(l))**(firstRoot-1) and
        den = lambda_pr(inv(X(l))) all in poly form. */
        for ($j = $count - 1; $j >= 0; --$j) {
            $num1 = 0;

            for ($i = $degomega; $i >= 0; $i--) {
                if ($omega[$i] !== $this->blocksize) {
                    $num1 ^= $this->alphato[$this->modnn($omega[$i] + $i * $root[$j])];
                }
            }

            $num2 = $this->alphato[$this->modnn($root[$j] * ($this->firstroot - 1) + $this->blocksize)];
            $den  = 0;

            // lambda[i+1] for i even is the formal derivativelambda_pr of lambda[i].
            for ($i = min($deglambda, $this->numroots - 1) & ~1; $i >= 0; $i -= 2) {
                if ($lambda[$i + 1] !== $this->blocksize) {
                    $den ^= $this->alphato[$this->modnn($lambda[$i + 1] + $i * $root[$j])];
                }
            }

            // Apply error to data
            if ($num1 !== 0 && $loc[$j] >= $this->padding) {
                $data[$loc[$j] - $this->padding] = $data[$loc[$j] - $this->padding] ^ (
                    $this->alphato[$this->modnn(
                        $this->indexof[$num1] + $this->indexof[$num2] + $this->blocksize - $this->indexof[$den]
                    )]
                );
            }
        }

        if (null !== $erasures) {
            if (count($erasures) < $count) {
                $erasures->setSize($count);
            }

            for ($i = 0; $i < $count; $i++) {
                $erasures[$i] = $loc[$i];
            }
        }

        return $count;
    }

    /**
     * Computes $x % GF_SIZE, where GF_SIZE is 2**GF_BITS - 1, without a slow divide.
     */
    private function modnn(int $x): int {
        while ($x >= $this->blocksize) {
            $x -= $this->blocksize;
            $x = ($x >> $this->symbolsize) + ($x & $this->blocksize);
        }

        return $x;
    }
}
