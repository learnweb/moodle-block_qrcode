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

namespace BaconQrCode\Renderer;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Encoder\MatrixUtil;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use GdImage;

/**
 * Renderer for GDLib.
 *
 * @copyright 2025 Daniel Meißner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class GDLibRenderer implements RendererInterface
{
    /**
     * @var GdImage|null
     */
    private ?GdImage $image;

    /**
     * @var array<string, int>
     */
    private array $colors;

    /**
     * Constructor.
     *
     * @param int $size
     * @param int $margin
     * @param string $imageformat
     * @param int $compressionquality
     * @param Fill|null $fill
     */
    public function __construct(
        /**
         * @var int
         */
        private int $size,
        /**
         * @var int
         */
        private int $margin = 4,
        /**
         * @var string
         */
        private string $imageformat = 'png',
        /**
         * @var int
         */
        private int $compressionquality = 9,
        /**
         * @var Fill|null
         */
        private ?Fill $fill = null
    ) {
        if (! extension_loaded('gd') || ! function_exists('gd_info')) {
            throw new RuntimeException('You need to install the GD extension to use this back end');
        }

        if ($this->fill === null) {
            $this->fill = Fill::default();
        }
        if ($this->fill->has_gradient_fill()) {
            throw new InvalidArgumentException('GDLibRenderer does not support gradients');
        }
    }

    /**
     * Renders the QR code to an image.
     *
     * @throws InvalidArgumentException if matrix width doesn't match height
     */
    public function render(QrCode $qrcode): string {
        $matrix = $qrcode->get_matrix();
        $matrixsize = $matrix->get_width();

        if ($matrixsize !== $matrix->get_height()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        MatrixUtil::removepositiondetectionpatterns($matrix);
        $this->new_image();
        $this->draw($matrix);

        return $this->render_image();
    }

    /**
     * Creates a new image with the specified size and background color.
     *
     * @return void
     */
    private function new_image(): void {
        $img = imagecreatetruecolor($this->size, $this->size);
        if ($img === false) {
            throw new RuntimeException('Failed to create image of that size');
        }

        $this->image = $img;
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);

        $bg = $this->get_color($this->fill->get_background_color());
        imagefilledrectangle($this->image, 0, 0, $this->size, $this->size, $bg);
        imagealphablending($this->image, true);
    }

    /**
     * Draws the QR code to the image.
     *
     * @param ByteMatrix $matrix
     * @return void
     */
    private function draw(ByteMatrix $matrix): void {
        $matrixsize = $matrix->get_width();

        $pointsonside = $matrix->get_width() + $this->margin * 2;
        $pointinpx = $this->size / $pointsonside;

        $this->draw_eye(0, 0, $pointinpx, $this->fill->get_top_left_eyefill());
        $this->draw_eye($matrixsize - 7, 0, $pointinpx, $this->fill->get_top_right_eyefill());
        $this->draw_eye(0, $matrixsize - 7, $pointinpx, $this->fill->get_bottom_left_eyefill());

        $rows = $matrix->get_array()->toArray();
        $color = $this->get_color($this->fill->get_foreground_color());
        for ($y = 0; $y < $matrixsize; $y += 1) {
            for ($x = 0; $x < $matrixsize; $x += 1) {
                if (! $rows[$y][$x]) {
                    continue;
                }

                $points = $this->normalize_points([
                    ($this->margin + $x) * $pointinpx, ($this->margin + $y) * $pointinpx,
                    ($this->margin + $x + 1) * $pointinpx, ($this->margin + $y) * $pointinpx,
                    ($this->margin + $x + 1) * $pointinpx, ($this->margin + $y + 1) * $pointinpx,
                    ($this->margin + $x) * $pointinpx, ($this->margin + $y + 1) * $pointinpx,
                ]);
                imagefilledpolygon($this->image, $points, $color);
            }
        }
    }

    /**
     * Draws an eye to the image.
     *
     * @param int $xoffset
     * @param int $yoffset
     * @param float $pointinpx
     * @param EyeFill $eyefill
     * @return void
     */
    private function draw_eye(int $xoffset, int $yoffset, float $pointinpx, EyeFill $eyefill): void {
        $internalcolor = $this->get_color($eyefill->inherits_internal_color()
            ? $this->fill->get_foreground_color()
            : $eyefill->get_internal_color());

        $externalcolor = $this->get_color($eyefill->inherits_external_color()
            ? $this->fill->get_foreground_color()
            : $eyefill->get_external_color());

        for ($y = 0; $y < 7; $y += 1) {
            for ($x = 0; $x < 7; $x += 1) {
                if ((($y === 1 || $y === 5) && $x > 0 && $x < 6) || (($x === 1 || $x === 5) && $y > 0 && $y < 6)) {
                    continue;
                }

                $points = $this->normalize_points([
                    ($this->margin + $x + $xoffset) * $pointinpx, ($this->margin + $y + $yoffset) * $pointinpx,
                    ($this->margin + $x + $xoffset + 1) * $pointinpx, ($this->margin + $y + $yoffset) * $pointinpx,
                    ($this->margin + $x + $xoffset + 1) * $pointinpx, ($this->margin + $y + $yoffset + 1) * $pointinpx,
                    ($this->margin + $x + $xoffset) * $pointinpx, ($this->margin + $y + $yoffset + 1) * $pointinpx,
                ]);

                if ($y > 1 && $y < 5 && $x > 1 && $x < 5) {
                    imagefilledpolygon($this->image, $points, $internalcolor);
                } else {
                    imagefilledpolygon($this->image, $points, $externalcolor);
                }
            }
        }
    }

    /**
     * Normalize points will trim right and bottom line by 1 pixel.
     * Otherwise pixels of neighbors are overlapping which leads to issue with transparency and small QR codes.
     *
     * @param array $points
     * @return array
     */
    private function normalize_points(array $points): array {
        $maxx = $maxy = 0;
        for ($i = 0; $i < count($points); $i += 2) {
            // Do manual round as GD just removes decimal part.
            $points[$i] = $newx = round($points[$i]);
            $points[$i + 1] = $newy = round($points[$i + 1]);

            $maxx = max($maxx, $newx);
            $maxy = max($maxy, $newy);
        }

        // Do trimming only if there are 4 points (8 coordinates), assumes this is square.

        for ($i = 0; $i < count($points); $i += 2) {
            $points[$i] = min($points[$i], $maxx - 1);
            $points[$i + 1] = min($points[$i + 1], $maxy - 1);
        }

        return $points;
    }

    /**
     * Renders the image to a string.
     *
     * @return string
     */
    private function render_image(): string {
        ob_start();
        $quality = $this->compressionquality;
        switch ($this->imageformat) {
            case 'png':
                if ($quality > 9 || $quality < 0) {
                    $quality = 9;
                }
                imagepng($this->image, null, $quality);
                break;

            case 'gif':
                imagegif($this->image, null);
                break;

            case 'jpeg':
            case 'jpg':
                if ($quality > 100 || $quality < 0) {
                    $quality = 85;
                }
                imagejpeg($this->image, null, $quality);
                break;
            default:
                ob_end_clean();
                throw new InvalidArgumentException(
                    'Supported image formats are jpeg, png and gif, got: ' . $this->imageformat
                );
        }

        imagedestroy($this->image);
        $this->colors = [];
        $this->image = null;

        return ob_get_clean();
    }

    /**
     * Returns the color identifier for the given color, allocating it if necessary.
     *
     * @param ColorInterface $color
     * @return int
     */
    private function get_color(ColorInterface $color): int {
        $alpha = 100;

        if ($color instanceof Alpha) {
            $alpha = $color->get_alpha();
            $color = $color->get_base_color();
        }

        $rgb = $color->to_rgb();

        $colorkey = sprintf('%02X%02X%02X%02X', $rgb->get_red(), $rgb->get_green(), $rgb->get_blue(), $alpha);

        if (! isset($this->colors[$colorkey])) {
            $colorid = imagecolorallocatealpha(
                $this->image,
                $rgb->get_red(),
                $rgb->get_green(),
                $rgb->get_blue(),
                (int)((100 - $alpha) / 100 * 127) // Alpha for GD is in range 0 (opaque) - 127 (transparent).
            );

            if ($colorid === false) {
                throw new RuntimeException('Failed to create color: #' . $colorkey);
            }

            $this->colors[$colorkey] = $colorid;
        }

        return $this->colors[$colorkey];
    }
}
