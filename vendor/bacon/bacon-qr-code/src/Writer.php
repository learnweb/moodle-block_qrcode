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

namespace BaconQrCode;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Version;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Renderer\RendererInterface;

/**
 * QR code writer.
 *
 * @copyright 2024 Justus Dieckmann
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class Writer
{
    /**
     * @var RendererInterface
     */
    private readonly RendererInterface $renderer;

    /**
     * Creates a new writer with a specific renderer.
     */
    public function __construct($renderer) {
    }

    /**
     * Writes QR code and returns it as string.
     *
     * Content is a string which *should* be encoded in UTF-8, in case there are
     * non ASCII-characters present.
     *
     * @throws InvalidArgumentException if the content is empty
     */
    public function write_string(
        string $content,
        string $encoding = Encoder::DEFAULT_BYTE_MODE_ENCODING,
        ?ErrorCorrectionLevel $eclevel = null,
        ?Version $forcedversion = null
    ): string {
        if (strlen($content) === 0) {
            throw new InvalidArgumentException('Found empty contents');
        }

        if (null === $eclevel) {
            $eclevel = ErrorCorrectionLevel::L();
        }

        return $this->renderer->render(Encoder::encode($content, $eclevel, $encoding, $forcedversion));
    }

    /**
     * Writes QR code to a file.
     *
     * @see Writer::write_string()
     */
    public function write_file(
        string $content,
        string $filename,
        string $encoding = Encoder::DEFAULT_BYTE_MODE_ENCODING,
        ?ErrorCorrectionLevel $eclevel = null,
        ?Version $forcedversion = null
    ): void {
        file_put_contents($filename, $this->write_string($content, $encoding, $eclevel, $forcedversion));
    }
}
