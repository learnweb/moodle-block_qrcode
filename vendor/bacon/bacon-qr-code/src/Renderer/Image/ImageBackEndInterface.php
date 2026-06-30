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

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;

/**
 * Interface for back ends able to produce path based images.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface ImageBackEndInterface
{
    /**
     *  Starts a new image.
     *
     *  If a previous image was already started, previous data get erased.
     *
     * @param int $size
     * @param ColorInterface $backgroundcolor
     * @return void
     */
    public function new(int $size, ColorInterface $backgroundcolor): void;

    /**
     *  Transforms all following drawing operation coordinates by scaling them by a given factor.
     *
     * @param float $size
     * @return void
     * @throws RuntimeException if no image was started yet.
     */
    public function scale(float $size): void;

    /**
     *  Transforms all following drawing operation coordinates by translating them by a given amount.
     *
     * @param float $x
     * @param float $y
     * @return void
     * @throws RuntimeException if no image was started yet.
     */
    public function translate(float $x, float $y): void;

    /**
     *  Transforms all following drawing operation coordinates by rotating them by a given amount.
     *
     * @param int $degrees
     * @return void
     * @throws RuntimeException if no image was started yet.
     */
    public function rotate(int $degrees): void;

    /**
     *  Pushes the current coordinate transformation onto a stack.
     *
     * @return void
     * @throws RuntimeException if no image was started yet.
     */
    public function push(): void;

    /**
     *  Pops the last coordinate transformation from a stack.
     *
     * @return void
     * @throws RuntimeException if no image was started yet.
     */
    public function pop(): void;

    /**
     *  Draws a path with a given color.
     *
     * @param Path $path
     * @param ColorInterface $color
     * @return void
     *@throws RuntimeException if no image was started yet.
     */
    public function draw_path_with_color(Path $path, ColorInterface $color): void;

    /**
     *  Draws a path with a given gradient which spans the box described by the position and size.
     *
     * @param Path $path
     * @param Gradient $gradient
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @return void
     * @throws RuntimeException if no image was started yet.
     */
    public function draw_path_with_gradient(
        Path $path,
        Gradient $gradient,
        float $x,
        float $y,
        float $width,
        float $height
    ): void;

    /**
     *  Ends the image drawing operation and returns the resulting blob.
     *
     *  This should reset the state of the back end and thus this method should only be callable once per image.
     *
     * @return string
     * @throws RuntimeException if no image was started yet.
     */
    public function done(): string;
}
