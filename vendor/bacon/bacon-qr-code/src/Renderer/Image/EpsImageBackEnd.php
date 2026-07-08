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
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Curve;
use BaconQrCode\Renderer\Path\EllipticArc;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;

/**
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EpsImageBackEnd implements ImageBackEndInterface
{
    private const PRECISION = 3;

    /**
     * @var string|null
     */
    private ?string $eps;

    /**
     * @param int $size
     * @param ColorInterface $backgroundcolor
     * @return void
     */
    public function new(int $size, ColorInterface $backgroundcolor): void {
        $this->eps = "%!PS-Adobe-3.0 EPSF-3.0\n"
            . "%%Creator: BaconQrCode\n"
            . sprintf("%%%%BoundingBox: 0 0 %d %d \n", $size, $size)
            . "%%BeginProlog\n"
            . "save\n"
            . "50 dict begin\n"
            . "/q { gsave } bind def\n"
            . "/Q { grestore } bind def\n"
            . "/s { scale } bind def\n"
            . "/t { translate } bind def\n"
            . "/r { rotate } bind def\n"
            . "/n { newpath } bind def\n"
            . "/m { moveto } bind def\n"
            . "/l { lineto } bind def\n"
            . "/c { curveto } bind def\n"
            . "/z { closepath } bind def\n"
            . "/f { eofill } bind def\n"
            . "/rgb { setrgbcolor } bind def\n"
            . "/cmyk { setcmykcolor } bind def\n"
            . "/gray { setgray } bind def\n"
            . "%%EndProlog\n"
            . "1 -1 s\n"
            . sprintf("0 -%d t\n", $size);

        if ($backgroundcolor instanceof Alpha && 0 === $backgroundcolor->getAlpha()) {
            return;
        }

        $this->eps .= wordwrap(
            '0 0 m'
            . sprintf(' %s 0 l', (string) $size)
            . sprintf(' %s %s l', (string) $size, (string) $size)
            . sprintf(' 0 %s l', (string) $size)
            . ' z'
            . ' ' . $this->get_color_set_string($backgroundcolor) . " f\n",
            75,
            "\n "
        );
    }

    /**
     * @param float $size
     * @return void
     */
    public function scale(float $size): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= sprintf("%1\$s %1\$s s\n", round($size, self::PRECISION));
    }

    /**
     * @param float $x
     * @param float $y
     * @return void
     */
    public function translate(float $x, float $y): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= sprintf("%s %s t\n", round($x, self::PRECISION), round($y, self::PRECISION));
    }

    /**
     * @param int $degrees
     * @return void
     */
    public function rotate(int $degrees): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= sprintf("%d r\n", $degrees);
    }

    /**
     * @return void
     */
    public function push(): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= "q\n";
    }

    /**
     * @return void
     */
    public function pop(): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= "Q\n";
    }

    /**
     * @param Path $path
     * @param ColorInterface $color
     * @return void
     */
    public function draw_path_with_color(Path $path, ColorInterface $color): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $fromX = 0;
        $fromY = 0;
        $this->eps .= wordwrap(
            'n '
            . $this->draw_path_operations($path, $fromX, $fromY)
            . ' ' . $this->get_color_set_string($color) . " f\n",
            75,
            "\n "
        );
    }

    /**
     * @param Path $path
     * @param Gradient $gradient
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @return void
     */
    public function draw_path_with_gradient(
        Path $path,
        Gradient $gradient,
        float $x,
        float $y,
        float $width,
        float $height
    ): void {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $fromX = 0;
        $fromY = 0;
        $this->eps .= wordwrap(
            'q n ' . $this->draw_path_operations($path, $fromX, $fromY) . "\n",
            75,
            "\n "
        );

        $this->create_gradient_fill($gradient, $x, $y, $width, $height);
    }

    /**
     * @return string
     */
    public function done(): string {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= "%%TRAILER\nend restore\n%%EOF";
        $blob = $this->eps;
        $this->eps = null;

        return $blob;
    }

    /**
     * @param iterable $ops
     * @param $fromx
     * @param $fromy
     * @return string
     */
    private function draw_path_operations(iterable $ops, &$fromx, &$fromy): string {
        $pathdata = [];

        foreach ($ops as $op) {
            switch (true) {
                case $op instanceof Move:
                    $fromx = $tox = round($op->getX(), self::PRECISION);
                    $fromy = $toy = round($op->getY(), self::PRECISION);
                    $pathdata[] = sprintf('%s %s m', $tox, $toy);
                    break;

                case $op instanceof Line:
                    $fromx = $tox = round($op->get_x(), self::PRECISION);
                    $fromy = $toy = round($op->get_y(), self::PRECISION);
                    $pathdata[] = sprintf('%s %s l', $tox, $toy);
                    break;

                case $op instanceof EllipticArc:
                    $pathdata[] = $this->draw_path_operations($op->to_curves($fromx, $fromy), $fromx, $fromy);
                    break;

                case $op instanceof Curve:
                    $x1 = round($op->get_x_1(), self::PRECISION);
                    $y1 = round($op->get_y_1(), self::PRECISION);
                    $x2 = round($op->get_x_2(), self::PRECISION);
                    $y2 = round($op->get_y_2(), self::PRECISION);
                    $fromx = $x3 = round($op->get_x_3(), self::PRECISION);
                    $fromy = $y3 = round($op->get_y_3(), self::PRECISION);
                    $pathdata[] = sprintf('%s %s %s %s %s %s c', $x1, $y1, $x2, $y2, $x3, $y3);
                    break;

                case $op instanceof Close:
                    $pathdata[] = 'z';
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        return implode(' ', $pathdata);
    }

    /**
     * @param Gradient $gradient
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @return void
     */
    private function create_gradient_fill(Gradient $gradient, float $x, float $y, float $width, float $height): void {
        $startcolor = $gradient->get_start_color();
        $endcolor = $gradient->get_end_color();

        if ($startcolor instanceof Alpha) {
            $startcolor = $startcolor->getBaseColor();
        }

        $startcolortype = get_class($startcolor);

        if (! in_array($startcolortype, [Rgb::class, Cmyk::class, Gray::class])) {
            $startcolortype = Cmyk::class;
            $startcolor = $startcolor->toCmyk();
        }

        if (get_class($endcolor) !== $startcolortype) {
            switch ($startcolortype) {
                case Cmyk::class:
                    $endcolor = $endcolor->toCmyk();
                    break;

                case Rgb::class:
                    $endcolor = $endcolor->toRgb();
                    break;

                case Gray::class:
                    $endcolor = $endcolor->toGray();
                    break;
            }
        }

        $this->eps .= "eoclip\n<<\n";

        if ($gradient->get_type() === GradientType::RADIAL()) {
            $this->eps .= " /ShadingType 3\n";
        } else {
            $this->eps .= " /ShadingType 2\n";
        }

        $this->eps .= " /Extend [ true true ]\n"
            . " /AntiAlias true\n";

        switch ($startcolortype) {
            case Cmyk::class:
                $this->eps .= " /ColorSpace /DeviceCMYK\n";
                break;

            case Rgb::class:
                $this->eps .= " /ColorSpace /DeviceRGB\n";
                break;

            case Gray::class:
                $this->eps .= " /ColorSpace /DeviceGray\n";
                break;
        }

        switch ($gradient->get_type()) {
            case GradientType::HORIZONTAL():
                $this->eps .= sprintf(
                    " /Coords [ %s %s %s %s ]\n",
                    round($x, self::PRECISION),
                    round($y, self::PRECISION),
                    round($x + $width, self::PRECISION),
                    round($y, self::PRECISION)
                );
                break;

            case GradientType::VERTICAL():
                $this->eps .= sprintf(
                    " /Coords [ %s %s %s %s ]\n",
                    round($x, self::PRECISION),
                    round($y, self::PRECISION),
                    round($x, self::PRECISION),
                    round($y + $height, self::PRECISION)
                );
                break;

            case GradientType::DIAGONAL():
                $this->eps .= sprintf(
                    " /Coords [ %s %s %s %s ]\n",
                    round($x, self::PRECISION),
                    round($y, self::PRECISION),
                    round($x + $width, self::PRECISION),
                    round($y + $height, self::PRECISION)
                );
                break;

            case GradientType::INVERSE_DIAGONAL():
                $this->eps .= sprintf(
                    " /Coords [ %s %s %s %s ]\n",
                    round($x, self::PRECISION),
                    round($y + $height, self::PRECISION),
                    round($x + $width, self::PRECISION),
                    round($y, self::PRECISION)
                );
                break;

            case GradientType::RADIAL():
                $centerX = ($x + $width) / 2;
                $centerY = ($y + $height) / 2;

                $this->eps .= sprintf(
                    " /Coords [ %s %s 0 %s %s %s ]\n",
                    round($centerX, self::PRECISION),
                    round($centerY, self::PRECISION),
                    round($centerX, self::PRECISION),
                    round($centerY, self::PRECISION),
                    round(max($width, $height) / 2, self::PRECISION)
                );
                break;
        }

        $this->eps .= " /Function\n"
            . " <<\n"
            . "  /FunctionType 2\n"
            . "  /Domain [ 0 1 ]\n"
            . sprintf("  /C0 [ %s ]\n", $this->get_color_string($startcolor))
            . sprintf("  /C1 [ %s ]\n", $this->get_color_string($endcolor))
            . "  /N 1\n"
            . " >>\n>>\nshfill\nQ\n";
    }

    /**
     * @param ColorInterface $color
     * @return string
     */
    private function get_color_set_string(ColorInterface $color): string {
        if ($color instanceof Rgb) {
            return $this->get_color_string($color) . ' rgb';
        }

        if ($color instanceof Cmyk) {
            return $this->get_color_string($color) . ' cmyk';
        }

        if ($color instanceof Gray) {
            return $this->get_color_string($color) . ' gray';
        }

        return $this->get_color_set_string($color->toCmyk());
    }

    /**
     * @param ColorInterface $color
     * @return string
     */
    private function get_color_string(ColorInterface $color): string {
        if ($color instanceof Rgb) {
            return sprintf('%s %s %s', $color->getRed() / 255, $color->getGreen() / 255, $color->getBlue() / 255);
        }

        if ($color instanceof Cmyk) {
            return sprintf(
                '%s %s %s %s',
                $color->getCyan() / 100,
                $color->getMagenta() / 100,
                $color->getYellow() / 100,
                $color->getBlack() / 100
            );
        }

        if ($color instanceof Gray) {
            return sprintf('%s', $color->getGray() / 100);
        }

        return $this->get_color_string($color->toCmyk());
    }
}
