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
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ImagickImageBackEnd implements ImageBackEndInterface
{
    /**
     * @var string
     */
    private string $imageformat;
    /**
     * @var int
     */
    private int $compressionquality;
    /**
     * @var Imagick|null
     */
    private ?Imagick $image;
    /**
     * @var ImagickDraw|null
     */
    private ?ImagickDraw $draw;
    /**
     * @var int|null
     */
    private ?int $gradientcount;
    /**
     * @var TransformationMatrix[]|null
     */
    private ?array $matrices;
    /**
     * @var int|null
     */
    private ?int $matrixindex;

    /**
     * Constructor.
     *
     * @param string $imageformat
     * @param int $compressionquality
     */
    public function __construct(string $imageformat = 'png', int $compressionquality = 100) {
        if (! class_exists(Imagick::class)) {
            throw new RuntimeException('You need to install the imagick extension to use this back end');
        }

        $this->imageformat = $imageformat;
        $this->compressionquality = $compressionquality;
    }

    /**
     * @param int $size
     * @param ColorInterface $backgroundcolor
     * @return void
     * @throws \ImagickException
     * @throws \ImagickPixelException
     */
    public function new(int $size, ColorInterface $backgroundcolor): void {
        $this->image = new Imagick();
        $this->image->newImage($size, $size, $this->get_color_pixel($backgroundcolor));
        $this->image->setImageFormat($this->imageformat);
        $this->image->setCompressionQuality($this->compressionquality);
        $this->draw = new ImagickDraw();
        $this->gradientcount = 0;
        $this->matrices = [new TransformationMatrix()];
        $this->matrixindex = 0;
    }

    /**
     * @param float $size
     * @return void
     */
    public function scale(float $size): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->scale($size, $size);
        $this->matrices[$this->matrixindex] = $this->matrices[$this->matrixindex]
            ->multiply(TransformationMatrix::scale($size));
    }

    /**
     * @param float $x
     * @param float $y
     * @return void
     */
    public function translate(float $x, float $y): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->translate($x, $y);
        $this->matrices[$this->matrixindex] = $this->matrices[$this->matrixindex]
            ->multiply(TransformationMatrix::translate($x, $y));
    }

    /**
     * @param int $degrees
     * @return void
     */
    public function rotate(int $degrees): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->rotate($degrees);
        $this->matrices[$this->matrixindex] = $this->matrices[$this->matrixindex]
            ->multiply(TransformationMatrix::rotate($degrees));
    }

    /**
     * @return void
     * @throws \ImagickException
     */
    public function push(): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->push();
        $this->matrices[++$this->matrixindex] = $this->matrices[$this->matrixindex - 1];
    }

    /**
     * @return void
     * @throws \ImagickException
     */
    public function pop(): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->pop();
        unset($this->matrices[$this->matrixindex--]);
    }

    /**
     * @param Path $path
     * @param ColorInterface $color
     * @return void
     * @throws \ImagickDrawException
     * @throws \ImagickPixelException
     */
    public function draw_path_with_color(Path $path, ColorInterface $color): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->setFillColor($this->get_color_pixel($color));
        $this->draw_path($path);
    }

    /**
     * @param Path $path
     * @param Gradient $gradient
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @return void
     * @throws \ImagickException
     * @throws \ImagickPixelException
     */
    public function draw_path_with_gradient(
        Path $path,
        Gradient $gradient,
        float $x,
        float $y,
        float $width,
        float $height
    ): void {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->setFillPatternURL('#' . $this->create_gradient_fill($gradient, $x, $y, $width, $height));
        $this->draw_path($path);
    }

    /**
     * @return string
     * @throws \ImagickException
     */
    public function done(): string {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->image->drawImage($this->draw);
        $blob = $this->image->getImageBlob();
        $this->draw->clear();
        $this->image->clear();
        $this->draw = null;
        $this->image = null;
        $this->gradientcount = null;

        return $blob;
    }

    /**
     * @param Path $path
     * @return void
     */
    private function draw_path(Path $path): void {
        $this->draw->pathStart();

        foreach ($path as $op) {
            switch (true) {
                case $op instanceof Move:
                    $this->draw->pathMoveToAbsolute($op->getX(), $op->getY());
                    break;

                case $op instanceof Line:
                    $this->draw->pathLineToAbsolute($op->get_x(), $op->get_y());
                    break;

                case $op instanceof EllipticArc:
                    $this->draw->pathEllipticArcAbsolute(
                        $op->get_x_radius(),
                        $op->get_y_radius(),
                        $op->get_x_axis_angle(),
                        $op->is_large_arc(),
                        $op->is_sweep(),
                        $op->get_x(),
                        $op->get_y()
                    );
                    break;

                case $op instanceof Curve:
                    $this->draw->pathCurveToAbsolute(
                        $op->get_x_1(),
                        $op->get_y_1(),
                        $op->get_x_2(),
                        $op->get_y_2(),
                        $op->get_x_3(),
                        $op->get_y_3()
                    );
                    break;

                case $op instanceof Close:
                    $this->draw->pathClose();
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        $this->draw->pathFinish();
    }

    /**
     * @param Gradient $gradient
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @return string
     * @throws \ImagickException
     * @throws \ImagickPixelException
     */
    private function create_gradient_fill(Gradient $gradient, float $x, float $y, float $width, float $height): string {
        [$width, $height] = $this->matrices[$this->matrixindex]->apply($width, $height);

        $startcolor = $this->get_color_pixel($gradient->get_start_color())->getColorAsString();
        $endcolor = $this->get_color_pixel($gradient->get_end_color())->getColorAsString();
        $gradientimage = new Imagick();

        switch ($gradient->get_type()) {
            case GradientType::HORIZONTAL():
                $gradientimage->newPseudoImage((int) $height, (int) $width, sprintf(
                    'gradient:%s-%s',
                    $startcolor,
                    $endcolor
                ));
                $gradientimage->rotateImage('transparent', -90);
                break;

            case GradientType::VERTICAL():
                $gradientimage->newPseudoImage((int) $width, (int) $height, sprintf(
                    'gradient:%s-%s',
                    $startcolor,
                    $endcolor
                ));
                break;

            case GradientType::DIAGONAL():
            case GradientType::INVERSE_DIAGONAL():
                $gradientimage->newPseudoImage((int) ($width * sqrt(2)), (int) ($height * sqrt(2)), sprintf(
                    'gradient:%s-%s',
                    $startcolor,
                    $endcolor
                ));

                if (GradientType::DIAGONAL() === $gradient->get_type()) {
                    $gradientimage->rotateImage('transparent', -45);
                } else {
                    $gradientimage->rotateImage('transparent', -135);
                }

                $rotatedWidth = $gradientimage->getImageWidth();
                $rotatedHeight = $gradientimage->getImageHeight();

                $gradientimage->setImagePage($rotatedWidth, $rotatedHeight, 0, 0);
                $gradientimage->cropImage(
                    intdiv($rotatedWidth, 2) - 2,
                    intdiv($rotatedHeight, 2) - 2,
                    intdiv($rotatedWidth, 4) + 1,
                    intdiv($rotatedWidth, 4) + 1
                );
                break;

            case GradientType::RADIAL():
                $gradientimage->newPseudoImage((int) $width, (int) $height, sprintf(
                    'radial-gradient:%s-%s',
                    $startcolor,
                    $endcolor
                ));
                break;
        }

        $id = sprintf('g%d', ++$this->gradientcount);
        $this->draw->pushPattern($id, 0, 0, $width, $height);
        $this->draw->composite(Imagick::COMPOSITE_COPY, 0, 0, $width, $height, $gradientimage);
        $this->draw->popPattern();
        return $id;
    }

    /**
     * @param ColorInterface $color
     * @return ImagickPixel
     * @throws \ImagickPixelException
     */
    private function get_color_pixel(ColorInterface $color): ImagickPixel {
        $alpha = 100;

        if ($color instanceof Alpha) {
            $alpha = $color->getAlpha();
            $color = $color->getBaseColor();
        }

        if ($color instanceof Rgb) {
            return new ImagickPixel(sprintf(
                'rgba(%d, %d, %d, %F)',
                $color->getRed(),
                $color->getGreen(),
                $color->getBlue(),
                $alpha / 100
            ));
        }

        if ($color instanceof Cmyk) {
            return new ImagickPixel(sprintf(
                'cmyka(%d, %d, %d, %d, %F)',
                $color->getCyan(),
                $color->getMagenta(),
                $color->getYellow(),
                $color->getBlack(),
                $alpha / 100
            ));
        }

        if ($color instanceof Gray) {
            return new ImagickPixel(sprintf(
                'graya(%d%%, %F)',
                $color->getGray(),
                $alpha / 100
            ));
        }

        return $this->get_color_pixel(new Alpha($alpha, $color->toRgb()));
    }
}
