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

namespace BaconQrCode\Renderer\Path;

/**
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EllipticArc implements OperationInterface
{
    private const ZERO_TOLERANCE = 1e-05;
    /**
     * @var float|int
     */
    private float $xradius;
    /**
     * @var float|int
     */
    private float $yradius;
    /**
     * @var float|int
     */
    private float $xaxisangle;

    /**
     * @param float $xradius
     * @param float $yradius
     * @param float $xaxisangle
     * @param bool $largearc
     * @param bool $sweep
     * @param float $x
     * @param float $y
     */
    public function __construct(
        float $xradius,
        float $yradius,
        float $xaxisangle,
        private readonly bool $largearc,
        private readonly bool $sweep,
        private readonly float $x,
        private readonly float $y
    ) {
        $this->xradius = abs($xradius);
        $this->yradius = abs($yradius);
        $this->xaxisangle = $xaxisangle % 360;
    }

    /**
     * @return float
     */
    public function get_x_radius(): float {
        return $this->xradius;
    }

    /**
     * @return float
     */
    public function get_y_radius(): float {
        return $this->yradius;
    }

    /**
     * @return float
     */
    public function get_x_axis_angle(): float {
        return $this->xaxisangle;
    }

    /**
     * @return bool
     */
    public function is_large_arc(): bool {
        return $this->largearc;
    }

    /**
     * @return bool
     */
    public function is_sweep(): bool {
        return $this->sweep;
    }

    /**
     * @return float
     */
    public function get_x(): float {
        return $this->x;
    }

    /**
     * @return float
     */
    public function get_y(): float {
        return $this->y;
    }

    /**
     * @param float $x
     * @param float $y
     * @return OperationInterface
     */
    public function translate(float $x, float $y): OperationInterface {
        return new self(
            $this->xradius,
            $this->yradius,
            $this->xaxisangle,
            $this->largearc,
            $this->sweep,
            $this->x + $x,
            $this->y + $y
        );
    }

    /**
     * @param int $degrees
     * @return OperationInterface
     */
    public function rotate(int $degrees): OperationInterface {
        $radians = deg2rad($degrees);
        $sin = sin($radians);
        $cos = cos($radians);
        $xr = $this->x * $cos - $this->y * $sin;
        $yr = $this->x * $sin + $this->y * $cos;
        return new self(
            $this->xradius,
            $this->yradius,
            $this->xaxisangle,
            $this->largearc,
            $this->sweep,
            $xr,
            $yr
        );
    }

    /**
     * Converts the elliptic arc to multiple curves.
     *
     * Since not all image back ends support elliptic arcs, this method allows to convert the arc into multiple curves
     * resembling the same result.
     *
     * @see https://mortoray.com/2017/02/16/rendering-an-svg-elliptical-arc-as-bezier-curves/
     * @return array<Curve|Line>
     */
    public function to_curves(float $fromX, float $fromY): array {
        if (sqrt(($fromX - $this->x) ** 2 + ($fromY - $this->y) ** 2) < self::ZERO_TOLERANCE) {
            return [];
        }

        if ($this->xradius < self::ZERO_TOLERANCE || $this->yradius < self::ZERO_TOLERANCE) {
            return [new Line($this->x, $this->y)];
        }

        return $this->create_curves($fromX, $fromY);
    }

    /**
     * @return Curve[]
     */
    private function create_curves(float $fromx, float $fromy): array {
        $xangle = deg2rad($this->xaxisangle);
        [$centerX, $centerY, $radiusX, $radiusY, $startAngle, $deltaAngle] =
            $this->calculateCenterPointParameters($fromx, $fromy, $xangle);

        $s = $startAngle;
        $e = $s + $deltaAngle;
        $sign = ($e < $s) ? -1 : 1;
        $remain = abs($e - $s);
        $p1 = self::point($centerX, $centerY, $radiusX, $radiusY, $xangle, $s);
        $curves = [];

        while ($remain > self::ZERO_TOLERANCE) {
            $step = min($remain, pi() / 2);
            $signStep = $step * $sign;
            $p2 = self::point($centerX, $centerY, $radiusX, $radiusY, $xangle, $s + $signStep);

            $alphaT = tan($signStep / 2);
            $alpha = sin($signStep) * (sqrt(4 + 3 * $alphaT ** 2) - 1) / 3;
            $d1 = self::derivative($radiusX, $radiusY, $xangle, $s);
            $d2 = self::derivative($radiusX, $radiusY, $xangle, $s + $signStep);

            $curves[] = new Curve(
                $p1[0] + $alpha * $d1[0],
                $p1[1] + $alpha * $d1[1],
                $p2[0] - $alpha * $d2[0],
                $p2[1] - $alpha * $d2[1],
                $p2[0],
                $p2[1]
            );

            $s += $signStep;
            $remain -= $step;
            $p1 = $p2;
        }

        return $curves;
    }

    /**
     * @return float[]
     */
    private function calculateCenterPointParameters(float $fromX, float $fromY, float $xAngle): array {
        $rX = $this->xradius;
        $rY = $this->yradius;

        // F.6.5.1
        $dx2 = ($fromX - $this->x) / 2;
        $dy2 = ($fromY - $this->y) / 2;
        $x1p = cos($xAngle) * $dx2 + sin($xAngle) * $dy2;
        $y1p = -sin($xAngle) * $dx2 + cos($xAngle) * $dy2;

        // F.6.5.2
        $rxs = $rX ** 2;
        $rys = $rY ** 2;
        $x1ps = $x1p ** 2;
        $y1ps = $y1p ** 2;
        $cr = $x1ps / $rxs + $y1ps / $rys;

        if ($cr > 1) {
            $s = sqrt($cr);
            $rX *= $s;
            $rY *= $s;
            $rxs = $rX ** 2;
            $rys = $rY ** 2;
        }

        $dq = ($rxs * $y1ps + $rys * $x1ps);
        $pq = ($rxs * $rys - $dq) / $dq;
        $q = sqrt(max(0, $pq));

        if ($this->largearc === $this->sweep) {
            $q = -$q;
        }

        $cxp = $q * $rX * $y1p / $rY;
        $cyp = -$q * $rY * $x1p / $rX;

        // F.6.5.3
        $cx = cos($xAngle) * $cxp - sin($xAngle) * $cyp + ($fromX + $this->x) / 2;
        $cy = sin($xAngle) * $cxp + cos($xAngle) * $cyp + ($fromY + $this->y) / 2;

        // F.6.5.5
        $theta = self::angle(1, 0, ($x1p - $cxp) / $rX, ($y1p - $cyp) / $rY);

        // F.6.5.6
        $delta = self::angle(($x1p - $cxp) / $rX, ($y1p - $cyp) / $rY, (-$x1p - $cxp) / $rX, (-$y1p - $cyp) / $rY);
        $delta = fmod($delta, pi() * 2);

        if (! $this->sweep) {
            $delta -= 2 * pi();
        }

        return [$cx, $cy, $rX, $rY, $theta, $delta];
    }

    private static function angle(float $ux, float $uy, float $vx, float $vy): float {
        // F.6.5.4
        $dot = $ux * $vx + $uy * $vy;
        $length = sqrt($ux ** 2 + $uy ** 2) * sqrt($vx ** 2 + $vy ** 2);
        $angle = acos(min(1, max(-1, $dot / $length)));

        if (($ux * $vy - $uy * $vx) < 0) {
            return -$angle;
        }

        return $angle;
    }

    /**
     * @return float[]
     */
    private static function point(
        float $centerX,
        float $centerY,
        float $radiusX,
        float $radiusY,
        float $xAngle,
        float $angle
    ): array {
        return [
            $centerX + $radiusX * cos($xAngle) * cos($angle) - $radiusY * sin($xAngle) * sin($angle),
            $centerY + $radiusX * sin($xAngle) * cos($angle) + $radiusY * cos($xAngle) * sin($angle),
        ];
    }

    /**
     * @return float[]
     */
    private static function derivative(float $radiusX, float $radiusY, float $xAngle, float $angle): array {
        return [
            -$radiusX * cos($xAngle) * sin($angle) - $radiusY * sin($xAngle) * cos($angle),
            -$radiusX * sin($xAngle) * sin($angle) + $radiusY * cos($xAngle) * cos($angle),
        ];
    }
}
