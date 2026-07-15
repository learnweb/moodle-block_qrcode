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

use BaconQrCode\Encoder\MatrixUtil;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

/**
 * Image renderer.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ImageRenderer implements RendererInterface
{
    /**
     * Constructor.
     *
     * @param RendererStyle $rendererstyle
     * @param ImageBackEndInterface $imagebackend
     */
    public function __construct(
        /**
         * @var RendererStyle
         */
        private readonly RendererStyle $rendererstyle,
        /**
         * @var ImageBackEndInterface
         */
        private readonly ImageBackEndInterface $imagebackend
    ) {
    }

    /**
     * Renders the QR code to an image.
     *
     * @throws InvalidArgumentException if matrix width doesn't match height
     */
    public function render(QrCode $qrcode): string {
        $size = $this->rendererstyle->get_size();
        $margin = $this->rendererstyle->get_margin();
        $matrix = $qrcode->get_matrix();
        $matrixsize = $matrix->get_width();

        if ($matrixsize !== $matrix->get_height()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        $totalsize = $matrixsize + ($margin * 2);
        $modulesize = $size / $totalsize;
        $fill = $this->rendererstyle->get_fill();

        $this->imagebackend->new($size, $fill->get_background_color());
        $this->imagebackend->scale((float) $modulesize);
        $this->imagebackend->translate((float) $margin, (float) $margin);

        $module = $this->rendererstyle->get_module();
        $modulematrix = clone $matrix;
        MatrixUtil::removepositiondetectionpatterns($modulematrix);
        $modulepath = $this->draw_eyes($matrixsize, $module->create_path($modulematrix));

        if ($fill->has_gradient_fill()) {
            $this->imagebackend->draw_path_with_gradient(
                $modulepath,
                $fill->get_foreground_gradient(),
                0,
                0,
                $matrixsize,
                $matrixsize
            );
        } else {
            $this->imagebackend->draw_path_with_color($modulepath, $fill->get_foreground_color());
        }

        return $this->imagebackend->done();
    }

    /**
     * Draws the eyes of the QR code.
     *
     * @param int $matrixsize
     * @param Path $modulepath
     * @return Path
     */
    private function draw_eyes(int $matrixsize, Path $modulepath): Path {
        $fill = $this->rendererstyle->get_fill();

        $eye = $this->rendererstyle->get_eye();
        $externalpath = $eye->get_external_path();
        $internalpath = $eye->get_internal_path();

        $modulepath = $this->draw_eye(
            $externalpath,
            $internalpath,
            $fill->get_top_left_eyefill(),
            3.5,
            3.5,
            0,
            $modulepath
        );
        $modulepath = $this->draw_eye(
            $externalpath,
            $internalpath,
            $fill->get_top_right_eyefill(),
            $matrixsize - 3.5,
            3.5,
            90,
            $modulepath
        );
        $modulepath = $this->draw_eye(
            $externalpath,
            $internalpath,
            $fill->get_bottom_left_eyefill(),
            3.5,
            $matrixsize - 3.5,
            -90,
            $modulepath
        );

        return $modulepath;
    }

    /**
     * Draws a single eye of the QR code.
     *
     * @param Path $externalpath
     * @param Path $internalpath
     * @param EyeFill $fill
     * @param float $xtranslation
     * @param float $ytranslation
     * @param int $rotation
     * @param Path $modulepath
     * @return Path
     */
    private function draw_eye(
        Path $externalpath,
        Path $internalpath,
        EyeFill $fill,
        float $xtranslation,
        float $ytranslation,
        int $rotation,
        Path $modulepath
    ): Path {
        if ($fill->inherits_both_colors()) {
            return $modulepath
                ->append(
                    $externalpath->rotate($rotation)->translate($xtranslation, $ytranslation)
                )
                ->append(
                    $internalpath->rotate($rotation)->translate($xtranslation, $ytranslation)
                );
        }

        $this->imagebackend->push();
        $this->imagebackend->translate($xtranslation, $ytranslation);

        if (0 !== $rotation) {
            $this->imagebackend->rotate($rotation);
        }

        if ($fill->inherits_external_color()) {
            $modulepath = $modulepath->append(
                $externalpath->rotate($rotation)->translate($xtranslation, $ytranslation)
            );
        } else {
            $this->imagebackend->draw_path_with_color($externalpath, $fill->get_external_color());
        }

        if ($fill->inherits_internal_color()) {
            $modulepath = $modulepath->append(
                $internalpath->rotate($rotation)->translate($xtranslation, $ytranslation)
            );
        } else {
            $this->imagebackend->draw_path_with_color($internalpath, $fill->get_internal_color());
        }

        $this->imagebackend->pop();

        return $modulepath;
    }
}
