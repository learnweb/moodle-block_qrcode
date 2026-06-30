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

final class ImageRenderer implements RendererInterface
{
    public function __construct(
        private readonly RendererStyle $rendererStyle,
        private readonly ImageBackEndInterface $imageBackEnd
    ) {
    }

    /**
     * @throws InvalidArgumentException if matrix width doesn't match height
     */
    public function render(QrCode $qrCode): string {
        $size = $this->rendererStyle->get_size();
        $margin = $this->rendererStyle->get_margin();
        $matrix = $qrCode->get_matrix();
        $matrixSize = $matrix->get_width();

        if ($matrixSize !== $matrix->get_height()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        $totalSize = $matrixSize + ($margin * 2);
        $moduleSize = $size / $totalSize;
        $fill = $this->rendererStyle->get_fill();

        $this->imageBackEnd->new($size, $fill->get_background_color());
        $this->imageBackEnd->scale((float) $moduleSize);
        $this->imageBackEnd->translate((float) $margin, (float) $margin);

        $module = $this->rendererStyle->get_module();
        $moduleMatrix = clone $matrix;
        MatrixUtil::removepositiondetectionpatterns($moduleMatrix);
        $modulePath = $this->drawEyes($matrixSize, $module->createPath($moduleMatrix));

        if ($fill->has_gradient_fill()) {
            $this->imageBackEnd->draw_path_with_gradient(
                $modulePath,
                $fill->get_foreground_gradient(),
                0,
                0,
                $matrixSize,
                $matrixSize
            );
        } else {
            $this->imageBackEnd->draw_path_with_color($modulePath, $fill->get_foreground_color());
        }

        return $this->imageBackEnd->done();
    }

    private function drawEyes(int $matrixSize, Path $modulePath): Path {
        $fill = $this->rendererStyle->get_fill();

        $eye = $this->rendererStyle->get_eye();
        $externalPath = $eye->getExternalPath();
        $internalPath = $eye->getInternalPath();

        $modulePath = $this->drawEye(
            $externalPath,
            $internalPath,
            $fill->get_top_left_eyefill(),
            3.5,
            3.5,
            0,
            $modulePath
        );
        $modulePath = $this->drawEye(
            $externalPath,
            $internalPath,
            $fill->get_top_right_eyefill(),
            $matrixSize - 3.5,
            3.5,
            90,
            $modulePath
        );
        $modulePath = $this->drawEye(
            $externalPath,
            $internalPath,
            $fill->get_bottom_left_eyefill(),
            3.5,
            $matrixSize - 3.5,
            -90,
            $modulePath
        );

        return $modulePath;
    }

    private function drawEye(
        Path $externalPath,
        Path $internalPath,
        EyeFill $fill,
        float $xTranslation,
        float $yTranslation,
        int $rotation,
        Path $modulePath
    ): Path {
        if ($fill->inheritsBothColors()) {
            return $modulePath
                ->append(
                    $externalPath->rotate($rotation)->translate($xTranslation, $yTranslation)
                )
                ->append(
                    $internalPath->rotate($rotation)->translate($xTranslation, $yTranslation)
                );
        }

        $this->imageBackEnd->push();
        $this->imageBackEnd->translate($xTranslation, $yTranslation);

        if (0 !== $rotation) {
            $this->imageBackEnd->rotate($rotation);
        }

        if ($fill->inheritsExternalColor()) {
            $modulePath = $modulePath->append(
                $externalPath->rotate($rotation)->translate($xTranslation, $yTranslation)
            );
        } else {
            $this->imageBackEnd->draw_path_with_color($externalPath, $fill->getExternalColor());
        }

        if ($fill->inheritsInternalColor()) {
            $modulePath = $modulePath->append(
                $internalPath->rotate($rotation)->translate($xTranslation, $yTranslation)
            );
        } else {
            $this->imageBackEnd->draw_path_with_color($internalPath, $fill->getInternalColor());
        }

        $this->imageBackEnd->pop();

        return $modulePath;
    }
}
