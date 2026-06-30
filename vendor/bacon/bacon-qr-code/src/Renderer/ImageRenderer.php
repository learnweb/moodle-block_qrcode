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
        $size = $this->rendererStyle->getSize();
        $margin = $this->rendererStyle->getMargin();
        $matrix = $qrCode->getMatrix();
        $matrixSize = $matrix->getWidth();

        if ($matrixSize !== $matrix->getHeight()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        $totalSize = $matrixSize + ($margin * 2);
        $moduleSize = $size / $totalSize;
        $fill = $this->rendererStyle->getFill();

        $this->imageBackEnd->new($size, $fill->getBackgroundColor());
        $this->imageBackEnd->scale((float) $moduleSize);
        $this->imageBackEnd->translate((float) $margin, (float) $margin);

        $module = $this->rendererStyle->getModule();
        $moduleMatrix = clone $matrix;
        MatrixUtil::removepositiondetectionpatterns($moduleMatrix);
        $modulePath = $this->drawEyes($matrixSize, $module->createPath($moduleMatrix));

        if ($fill->hasGradientFill()) {
            $this->imageBackEnd->drawPathWithGradient(
                $modulePath,
                $fill->getForegroundGradient(),
                0,
                0,
                $matrixSize,
                $matrixSize
            );
        } else {
            $this->imageBackEnd->drawPathWithColor($modulePath, $fill->getForegroundColor());
        }

        return $this->imageBackEnd->done();
    }

    private function drawEyes(int $matrixSize, Path $modulePath): Path {
        $fill = $this->rendererStyle->getFill();

        $eye = $this->rendererStyle->getEye();
        $externalPath = $eye->getExternalPath();
        $internalPath = $eye->getInternalPath();

        $modulePath = $this->drawEye(
            $externalPath,
            $internalPath,
            $fill->getTopLeftEyeFill(),
            3.5,
            3.5,
            0,
            $modulePath
        );
        $modulePath = $this->drawEye(
            $externalPath,
            $internalPath,
            $fill->getTopRightEyeFill(),
            $matrixSize - 3.5,
            3.5,
            90,
            $modulePath
        );
        $modulePath = $this->drawEye(
            $externalPath,
            $internalPath,
            $fill->getBottomLeftEyeFill(),
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
            $this->imageBackEnd->drawPathWithColor($externalPath, $fill->getExternalColor());
        }

        if ($fill->inheritsInternalColor()) {
            $modulePath = $modulePath->append(
                $internalPath->rotate($rotation)->translate($xTranslation, $yTranslation)
            );
        } else {
            $this->imageBackEnd->drawPathWithColor($internalPath, $fill->getInternalColor());
        }

        $this->imageBackEnd->pop();

        return $modulePath;
    }
}
