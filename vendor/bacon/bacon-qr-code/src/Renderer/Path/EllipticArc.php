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
 * Represents an elliptic arc path operation.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EllipticArc implements OperationInterface
{
    /**
     *
     */
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
     * Constructor.
     *
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
        /**
         * @var bool
         */
        private readonly bool $largearc,
        /**
         * @var bool
         */
        private readonly bool $sweep,
        /**
         * @var float
         */
        private readonly float $x,
        /**
         * @var float
         */
        private readonly float $y
    ) {
        $this->xradius = abs($xradius);
        $this->yradius = abs($yradius);
        $this->xaxisangle = $xaxisangle % 360;
    }

    /**
     * Returns the X radius.
     *
     * @return float
     */
    public function get_x_radius(): float {
        return $this->xradius;
    }

    /**
     * Returns the Y radius.
     *
     * @return float
     */
    public function get_y_radius(): float {
        return $this->yradius;
    }

    /**
     * Returns the X axis angle.
     *
     * @return float
     */
    public function get_x_axis_angle(): float {
        return $this->xaxisangle;
    }

    /**
     * Returns whether this is a large arc.
     *
     * @return bool
     */
    public function is_large_arc(): bool {
        return $this->largearc;
    }

    /**
     * Returns whether this is a sweep arc.
     *
     * @return bool
     */
    public function is_sweep(): bool {
        return $this->sweep;
    }

    /**
     * Returns the X value.
     *
     * @return float
     */
    public function get_x(): float {
        return $this->x;
    }

    /**
     * Returns the Y value.
     *
     * @return float
     */
    public function get_y(): float {
        return $this->y;
    }

    /**
     * Returns a translated elliptic arc operation.
     *
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
     * Returns a rotated elliptic arc operation.
     *
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
    public function to_curves(float $fromx, float $fromy): array {
        if (sqrt(($fromx - $this->x) ** 2 + ($fromy - $this->y) ** 2) < self::ZERO_TOLERANCE) {
            return [];
        }

        if ($this->xradius < self::ZERO_TOLERANCE || $this->yradius < self::ZERO_TOLERANCE) {
            return [new Line($this->x, $this->y)];
        }

        return $this->create_curves($fromx, $fromy);
    }

    /**
     * Creates curves for this elliptic arc.
     *
     * @return Curve[]
     */
    private function create_curves(float $fromx, float $fromy): array {
        $xangle = deg2rad($this->xaxisangle);
        [$centerx, $centery, $radiusx, $radiusy, $startangle, $deltaangle] =
            $this->calculate_center_point_parameters($fromx, $fromy, $xangle);

        $s = $startangle;
        $e = $s + $deltaangle;
        $sign = ($e < $s) ? -1 : 1;
        $remain = abs($e - $s);
        $p1 = self::point($centerx, $centery, $radiusx, $radiusy, $xangle, $s);
        $curves = [];

        while ($remain > self::ZERO_TOLERANCE) {
            $step = min($remain, pi() / 2);
            $signstep = $step * $sign;
            $p2 = self::point($centerx, $centery, $radiusx, $radiusy, $xangle, $s + $signstep);

            $alphat = tan($signstep / 2);
            $alpha = sin($signstep) * (sqrt(4 + 3 * $alphat ** 2) - 1) / 3;
            $d1 = self::derivative($radiusx, $radiusy, $xangle, $s);
            $d2 = self::derivative($radiusx, $radiusy, $xangle, $s + $signstep);

            $curves[] = new Curve(
                $p1[0] + $alpha * $d1[0],
                $p1[1] + $alpha * $d1[1],
                $p2[0] - $alpha * $d2[0],
                $p2[1] - $alpha * $d2[1],
                $p2[0],
                $p2[1]
            );

            $s += $signstep;
            $remain -= $step;
            $p1 = $p2;
        }

        return $curves;
    }

    /**
     * Calculates the center point parameters.
     *
     * @return float[]
     */
    private function calculate_center_point_parameters(float $fromx, float $fromy, float $xangle): array {
        $rx = $this->xradius;
        $ry = $this->yradius;

        // F.6.5.1.
        $dx2 = ($fromx - $this->x) / 2;
        $dy2 = ($fromy - $this->y) / 2;
        $x1p = cos($xangle) * $dx2 + sin($xangle) * $dy2;
        $y1p = -sin($xangle) * $dx2 + cos($xangle) * $dy2;

        // F.6.5.2.
        $rxs = $rx ** 2;
        $rys = $ry ** 2;
        $x1ps = $x1p ** 2;
        $y1ps = $y1p ** 2;
        $cr = $x1ps / $rxs + $y1ps / $rys;

        if ($cr > 1) {
            $s = sqrt($cr);
            $rx *= $s;
            $ry *= $s;
            $rxs = $rx ** 2;
            $rys = $ry ** 2;
        }

        $dq = ($rxs * $y1ps + $rys * $x1ps);
        $pq = ($rxs * $rys - $dq) / $dq;
        $q = sqrt(max(0, $pq));

        if ($this->largearc === $this->sweep) {
            $q = -$q;
        }

        $cxp = $q * $rx * $y1p / $ry;
        $cyp = -$q * $ry * $x1p / $rx;

        // F.6.5.3.
        $cx = cos($xangle) * $cxp - sin($xangle) * $cyp + ($fromx + $this->x) / 2;
        $cy = sin($xangle) * $cxp + cos($xangle) * $cyp + ($fromy + $this->y) / 2;

        // F.6.5.5.
        $theta = self::angle(1, 0, ($x1p - $cxp) / $rx, ($y1p - $cyp) / $ry);

        // F.6.5.6.
        $delta = self::angle(($x1p - $cxp) / $rx, ($y1p - $cyp) / $ry, (-$x1p - $cxp) / $rx, (-$y1p - $cyp) / $ry);
        $delta = fmod($delta, pi() * 2);

        if (! $this->sweep) {
            $delta -= 2 * pi();
        }

        return [$cx, $cy, $rx, $ry, $theta, $delta];
    }

    /**
     * Calculates the angle between two vectors.
     *
     * @param float $ux
     * @param float $uy
     * @param float $vx
     * @param float $vy
     * @return float
     */
    private static function angle(float $ux, float $uy, float $vx, float $vy): float {
        // F.6.5.4.
        $dot = $ux * $vx + $uy * $vy;
        $length = sqrt($ux ** 2 + $uy ** 2) * sqrt($vx ** 2 + $vy ** 2);
        $angle = acos(min(1, max(-1, $dot / $length)));

        if (($ux * $vy - $uy * $vx) < 0) {
            return -$angle;
        }

        return $angle;
    }

    /**
     * Calculates a point on the arc.
     *
     * @return float[]
     */
    private static function point(
        float $centerx,
        float $centery,
        float $radiusx,
        float $radiusy,
        float $xangle,
        float $angle
    ): array {
        return [
            $centerx + $radiusx * cos($xangle) * cos($angle) - $radiusy * sin($xangle) * sin($angle),
            $centery + $radiusx * sin($xangle) * cos($angle) + $radiusy * cos($xangle) * sin($angle),
        ];
    }

    /**
     * Calculates the derivative on the arc.
     *
     * @return float[]
     */
    private static function derivative(float $radiusx, float $radiusy, float $xangle, float $angle): array {
        return [
            -$radiusx * cos($xangle) * sin($angle) - $radiusy * sin($xangle) * cos($angle),
            -$radiusx * sin($xangle) * sin($angle) + $radiusy * cos($xangle) * cos($angle),
        ];
    }
}
