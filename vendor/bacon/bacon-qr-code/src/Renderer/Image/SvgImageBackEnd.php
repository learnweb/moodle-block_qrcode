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
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Curve;
use BaconQrCode\Renderer\Path\EllipticArc;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use XMLWriter;

/**
 * Renders QR code images as SVG.
 *
 * @copyright 2025 D. Meißner
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class SvgImageBackEnd implements ImageBackEndInterface
{
    /**
     * Decimal precision.
     */
    private const PRECISION = 3;
    /**
     * Scale format.
     */
    private const SCALE_FORMAT = 'scale(%.' . self::PRECISION . 'F)';
    /**
     * Translate format.
     */
    private const TRANSLATE_FORMAT = 'translate(%.' . self::PRECISION . 'F,%.' . self::PRECISION . 'F)';
    /**
     * @var XMLWriter|null
     */
    private ?XMLWriter $xmlwriter;
    /**
     * @var array|null
     */
    private ?array $stack;
    /**
     * @var int|null
     */
    private ?int $currentstack;
    /**
     * @var int|null
     */
    private ?int $gradientcount;

    /**
     * Constructor.
     */
    public function __construct() {
        if (! class_exists(XMLWriter::class)) {
            throw new RuntimeException('You need to install the libxml extension to use this back end');
        }
    }

    /**
     * Starts a new SVG image.
     *
     * @param int $size
     * @param ColorInterface $backgroundcolor
     * @return void
     */
    public function new(int $size, ColorInterface $backgroundcolor): void {
        $this->xmlwriter = new XMLWriter();
        $this->xmlwriter->openMemory();

        $this->xmlwriter->startDocument('1.0', 'UTF-8');
        $this->xmlwriter->startElement('svg');
        $this->xmlwriter->writeAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $this->xmlwriter->writeAttribute('version', '1.1');
        $this->xmlwriter->writeAttribute('width', (string) $size);
        $this->xmlwriter->writeAttribute('height', (string) $size);
        $this->xmlwriter->writeAttribute('viewBox', '0 0 ' . $size . ' ' . $size);

        $this->gradientcount = 0;
        $this->currentstack = 0;
        $this->stack[0] = 0;

        $alpha = 1;

        if ($backgroundcolor instanceof Alpha) {
            $alpha = $backgroundcolor->getAlpha() / 100;
        }

        if (0 === $alpha) {
            return;
        }

        $this->xmlwriter->startElement('rect');
        $this->xmlwriter->writeAttribute('x', '0');
        $this->xmlwriter->writeAttribute('y', '0');
        $this->xmlwriter->writeAttribute('width', (string) $size);
        $this->xmlwriter->writeAttribute('height', (string) $size);
        $this->xmlwriter->writeAttribute('fill', $this->get_color_string($backgroundcolor));

        if ($alpha < 1) {
            $this->xmlwriter->writeAttribute('fill-opacity', (string) $alpha);
        }

        $this->xmlwriter->endElement();
    }

    /**
     * Scales the current drawing context.
     *
     * @param float $size
     * @return void
     */
    public function scale(float $size): void {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        $this->xmlwriter->startElement('g');
        $this->xmlwriter->writeAttribute(
            'transform',
            sprintf(self::SCALE_FORMAT, round($size, self::PRECISION))
        );
        ++$this->stack[$this->currentstack];
    }

    /**
     * Translates the current drawing context.
     *
     * @param float $x
     * @param float $y
     * @return void
     */
    public function translate(float $x, float $y): void {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        $this->xmlwriter->startElement('g');
        $this->xmlwriter->writeAttribute(
            'transform',
            sprintf(self::TRANSLATE_FORMAT, round($x, self::PRECISION), round($y, self::PRECISION))
        );
        ++$this->stack[$this->currentstack];
    }

    /**
     * Rotates the current drawing context.
     *
     * @param int $degrees
     * @return void
     */
    public function rotate(int $degrees): void {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        $this->xmlwriter->startElement('g');
        $this->xmlwriter->writeAttribute('transform', sprintf('rotate(%d)', $degrees));
        ++$this->stack[$this->currentstack];
    }

    /**
     * Saves the current drawing context.
     *
     * @return void
     */
    public function push(): void {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        $this->xmlwriter->startElement('g');
        $this->stack[] = 1;
        ++$this->currentstack;
    }

    /**
     * Restores the previous drawing context.
     *
     * @return void
     */
    public function pop(): void {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        for ($i = 0; $i < $this->stack[$this->currentstack]; ++$i) {
            $this->xmlwriter->endElement();
        }

        array_pop($this->stack);
        --$this->currentstack;
    }

    /**
     * Draws a path with a color fill.
     *
     * @param Path $path
     * @param ColorInterface $color
     * @return void
     */
    public function draw_path_with_color(Path $path, ColorInterface $color): void {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        $alpha = 1;

        if ($color instanceof Alpha) {
            $alpha = $color->getAlpha() / 100;
        }

        $this->start_path_element($path);
        $this->xmlwriter->writeAttribute('fill', $this->get_color_string($color));

        if ($alpha < 1) {
            $this->xmlwriter->writeAttribute('fill-opacity', (string) $alpha);
        }

        $this->xmlwriter->endElement();
    }

    /**
     * Draws a path with a gradient fill.
     *
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
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        $gradientid = $this->create_gradient_fill($gradient, $x, $y, $width, $height);
        $this->start_path_element($path);
        $this->xmlwriter->writeAttribute('fill', 'url(#' . $gradientid . ')');
        $this->xmlwriter->endElement();
    }

    /**
     * Finishes the SVG image and returns its content.
     *
     * @return string
     */
    public function done(): string {
        if (null === $this->xmlwriter) {
            throw new RuntimeException('No image has been started');
        }

        foreach ($this->stack as $openelements) {
            for ($i = $openelements; $i > 0; --$i) {
                $this->xmlwriter->endElement();
            }
        }

        $this->xmlwriter->endDocument();
        $blob = $this->xmlwriter->outputMemory(true);
        $this->xmlwriter = null;
        $this->stack = null;
        $this->currentstack = null;
        $this->gradientcount = null;

        return $blob;
    }

    /**
     * Starts an SVG path element.
     *
     * @param Path $path
     * @return void
     */
    private function start_path_element(Path $path): void {
        $pathdata = [];

        foreach ($path as $op) {
            switch (true) {
                case $op instanceof Move:
                    $pathdata[] = sprintf(
                        'M%s %s',
                        round($op->get_x(), self::PRECISION),
                        round($op->get_y(), self::PRECISION)
                    );
                    break;

                case $op instanceof Line:
                    $pathdata[] = sprintf(
                        'L%s %s',
                        round($op->get_x(), self::PRECISION),
                        round($op->get_y(), self::PRECISION)
                    );
                    break;

                case $op instanceof EllipticArc:
                    $pathdata[] = sprintf(
                        'A%s %s %s %u %u %s %s',
                        round($op->get_x_radius(), self::PRECISION),
                        round($op->get_y_radius(), self::PRECISION),
                        round($op->get_x_axis_angle(), self::PRECISION),
                        $op->is_large_arc(),
                        $op->is_sweep(),
                        round($op->get_x(), self::PRECISION),
                        round($op->get_y(), self::PRECISION)
                    );
                    break;

                case $op instanceof Curve:
                    $pathdata[] = sprintf(
                        'C%s %s %s %s %s %s',
                        round($op->get_x_1(), self::PRECISION),
                        round($op->get_y_1(), self::PRECISION),
                        round($op->get_x_2(), self::PRECISION),
                        round($op->get_y_2(), self::PRECISION),
                        round($op->get_x_3(), self::PRECISION),
                        round($op->get_y_3(), self::PRECISION)
                    );
                    break;

                case $op instanceof Close:
                    $pathdata[] = 'Z';
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        $this->xmlwriter->startElement('path');
        $this->xmlwriter->writeAttribute('fill-rule', 'evenodd');
        $this->xmlwriter->writeAttribute('d', implode('', $pathdata));
    }

    /**
     * Creates a gradient fill definition.
     *
     * @param Gradient $gradient
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @return string
     */
    private function create_gradient_fill(Gradient $gradient, float $x, float $y, float $width, float $height): string {
        $this->xmlwriter->startElement('defs');

        $startcolor = $gradient->get_start_color();
        $endcolor = $gradient->get_end_color();

        if ($gradient->get_type() === GradientType::RADIAL()) {
            $this->xmlwriter->startElement('radialGradient');
        } else {
            $this->xmlwriter->startElement('linearGradient');
        }

        $this->xmlwriter->writeAttribute('gradientUnits', 'userSpaceOnUse');

        switch ($gradient->get_type()) {
            case GradientType::HORIZONTAL():
                $this->xmlwriter->writeAttribute('x1', (string) round($x, self::PRECISION));
                $this->xmlwriter->writeAttribute('y1', (string) round($y, self::PRECISION));
                $this->xmlwriter->writeAttribute('x2', (string) round($x + $width, self::PRECISION));
                $this->xmlwriter->writeAttribute('y2', (string) round($y, self::PRECISION));
                break;

            case GradientType::VERTICAL():
                $this->xmlwriter->writeAttribute('x1', (string) round($x, self::PRECISION));
                $this->xmlwriter->writeAttribute('y1', (string) round($y, self::PRECISION));
                $this->xmlwriter->writeAttribute('x2', (string) round($x, self::PRECISION));
                $this->xmlwriter->writeAttribute('y2', (string) round($y + $height, self::PRECISION));
                break;

            case GradientType::DIAGONAL():
                $this->xmlwriter->writeAttribute('x1', (string) round($x, self::PRECISION));
                $this->xmlwriter->writeAttribute('y1', (string) round($y, self::PRECISION));
                $this->xmlwriter->writeAttribute('x2', (string) round($x + $width, self::PRECISION));
                $this->xmlwriter->writeAttribute('y2', (string) round($y + $height, self::PRECISION));
                break;

            case GradientType::INVERSE_DIAGONAL():
                $this->xmlwriter->writeAttribute('x1', (string) round($x, self::PRECISION));
                $this->xmlwriter->writeAttribute('y1', (string) round($y + $height, self::PRECISION));
                $this->xmlwriter->writeAttribute('x2', (string) round($x + $width, self::PRECISION));
                $this->xmlwriter->writeAttribute('y2', (string) round($y, self::PRECISION));
                break;

            case GradientType::RADIAL():
                $this->xmlwriter->writeAttribute('cx', (string) round(($x + $width) / 2, self::PRECISION));
                $this->xmlwriter->writeAttribute('cy', (string) round(($y + $height) / 2, self::PRECISION));
                $this->xmlwriter->writeAttribute('r', (string) round(max($width, $height) / 2, self::PRECISION));
                break;
        }

        $tobehashed = $this->get_color_string($startcolor) . $this->get_color_string($endcolor) . $gradient->get_type();
        if ($startcolor instanceof Alpha) {
            $tobehashed .= (string) $startcolor->getAlpha();
        }
        $id = sprintf('g%d-%s', ++$this->gradientcount, hash('xxh64', $tobehashed));
        $this->xmlwriter->writeAttribute('id', $id);

        $this->xmlwriter->startElement('stop');
        $this->xmlwriter->writeAttribute('offset', '0%');
        $this->xmlwriter->writeAttribute('stop-color', $this->get_color_string($startcolor));

        if ($startcolor instanceof Alpha) {
            $this->xmlwriter->writeAttribute('stop-opacity', (string) $startcolor->getAlpha());
        }

        $this->xmlwriter->endElement();

        $this->xmlwriter->startElement('stop');
        $this->xmlwriter->writeAttribute('offset', '100%');
        $this->xmlwriter->writeAttribute('stop-color', $this->get_color_string($endcolor));

        if ($endcolor instanceof Alpha) {
            $this->xmlwriter->writeAttribute('stop-opacity', (string) $endcolor->getAlpha());
        }

        $this->xmlwriter->endElement();

        $this->xmlwriter->endElement();
        $this->xmlwriter->endElement();

        return $id;
    }

    /**
     * Returns the SVG color string.
     *
     * @param ColorInterface $color
     * @return string
     */
    private function get_color_string(ColorInterface $color): string {
        $color = $color->to_rgb();

        return sprintf(
            '#%02x%02x%02x',
            $color->getRed(),
            $color->getGreen(),
            $color->getBlue()
        );
    }
}
