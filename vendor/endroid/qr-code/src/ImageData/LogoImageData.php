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

namespace Endroid\QrCode\ImageData;

defined('MOODLE_INTERNAL') || die();

use Endroid\QrCode\Logo\LogoInterface;

/**
 * Represents the image data of a logo to be used in a QR code.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final readonly class LogoImageData
{
    /**
     * Creates a new instance of LogoImageData with the provided parameters.
     *
     * @param string $data
     * @param \GdImage|null $image
     * @param string $mimetype
     * @param int $width
     * @param int $height
     * @param bool $punchoutbackground
     */
    private function __construct(
        /**
         * @var string
         */
        private string $data,
        /**
         * @var \GdImage|null
         */
        private ?\GdImage $image,
        /**
         * @var string
         */
        private string $mimetype,
        /**
         * @var int
         */
        private int $width,
        /**
         * @var int
         */
        private int $height,
        /**
         * @var bool
         */
        private bool $punchoutbackground,
    ) {
    }

    /**
     * Creates a new instance of LogoImageData from a LogoInterface object.
     *
     * @param LogoInterface $logo
     * @return self
     * @throws \Exception
     */
    public static function create_for_logo(LogoInterface $logo): self {
        error_clear_last();
        $data = @file_get_contents($logo->get_path());

        if (!is_string($data)) {
            $errordetails = error_get_last()['message'] ?? 'invalid data';
            throw new \Exception(sprintf('Could not read logo image data from path "%s": %s', $logo->get_path(), $errordetails));
        }

        if (false !== filter_var($logo->get_path(), FILTER_VALIDATE_URL)) {
            $mimetype = self::detect_mime_type_from_url($logo->get_path());
        } else {
            $mimetype = self::detect_mime_type_from_path($logo->get_path());
        }

        $width = $logo->get_resize_to_width();
        $height = $logo->get_resize_to_height();

        if ('image/svg+xml' === $mimetype) {
            if (null === $width || null === $height) {
                throw new \Exception('SVG Logos require an explicitly set resize width and height');
            }

            return new self($data, null, $mimetype, $width, $height, $logo->get_punchout_background());
        }

        if (!function_exists('imagecreatefromstring')) {
            throw new \Exception('Function "imagecreatefromstring" does not exist: check your GD installation');
        }

        error_clear_last();
        $image = @imagecreatefromstring($data);

        if (!$image) {
            $errordetails = error_get_last()['message'] ?? 'invalid data';
            throw new \Exception(sprintf('Unable to parse image data at path "%s": %s', $logo->get_path(), $errordetails));
        }

        // No target width and height specified: use from original image.
        if (null !== $width && null !== $height) {
            return new self($data, $image, $mimetype, $width, $height, $logo->get_punchout_background());
        }

        // Only target width specified: calculate height.
        if (null !== $width && null === $height) {
            return new self(
                $data,
                $image,
                $mimetype,
                $width,
                intval(imagesy($image) * $width / imagesx($image)),
                $logo->get_punchout_background()
            );
        }

        // Only target height specified: calculate width.
        if (null === $width && null !== $height) {
            return new self(
                $data,
                $image,
                $mimetype,
                intval(imagesx($image) * $height / imagesy($image)),
                $height,
                $logo->get_punchout_background()
            );
        }

        return new self($data, $image, $mimetype, imagesx($image), imagesy($image), $logo->get_punchout_background());
    }

    /**
     * Returns the raw image data of the logo.
     *
     * @return string
     */
    public function get_data(): string {
        return $this->data;
    }

    /**
     * Returns the GD image resource of the logo.
     *
     * @return \GdImage
     * @throws \Exception
     */
    public function get_image(): \GdImage {
        if (!$this->image instanceof \GdImage) {
            throw new \Exception('SVG Images have no image resource');
        }

        return $this->image;
    }

    /**
     * Returns the MIME type of the logo image.
     *
     * @return string
     */
    public function get_mime_type(): string {
        return $this->mimetype;
    }

    /**
     * Returns the width of the logo image.
     *
     * @return int
     */
    public function get_width(): int {
        return $this->width;
    }

    /**
     * Returns the height of the logo image.
     *
     * @return int
     */
    public function get_height(): int {
        return $this->height;
    }

    /**
     * Returns whether the background of the logo should be punched out (made transparent).
     *
     * @return bool
     */
    public function get_punchout_background(): bool {
        return $this->punchoutbackground;
    }

    /**
     * Creates a data URI for the logo image, which can be used in HTML or CSS.
     *
     * @return string
     */
    public function create_data_uri(): string {
        return 'data:' . $this->mimetype . ';base64,' . base64_encode($this->data);
    }

    /**
     * Detects the MIME type of an image from a given URL by retrieving the headers and extracting the content type.
     *
     * @param string $url
     * @return string
     * @throws \Exception
     */
    private static function detect_mime_type_from_url(string $url): string {
        $headers = get_headers($url, true);

        if (!is_array($headers)) {
            throw new \Exception(sprintf('Could not retrieve headers to determine content type for logo URL "%s"', $url));
        }

        $headers = array_combine(array_map('strtolower', array_keys($headers)), $headers);

        if (!isset($headers['content-type'])) {
            throw new \Exception(sprintf('Content type could not be determined for logo URL "%s"', $url));
        }

        return is_array($headers['content-type']) ? $headers['content-type'][1] : $headers['content-type'];
    }

    /**
     * Detects the MIME type of an image from a given file path using the mime_content_type function.
     *
     * @param string $path
     * @return string
     * @throws \Exception
     */
    private static function detect_mime_type_from_path(string $path): string {
        if (!function_exists('mime_content_type')) {
            throw new \Exception('You need the ext-fileinfo extension to determine logo mime type');
        }

        error_clear_last();
        $mimetype = @mime_content_type($path);

        if (!is_string($mimetype)) {
            $errordetails = error_get_last()['message'] ?? 'invalid data';
            throw new \Exception(sprintf('Could not determine mime type: %s', $errordetails));
        }

        if (!preg_match('#^image/#', $mimetype)) {
            throw new \Exception('Logo path is not an image');
        }

        // Passing mime type image/svg results in invisible images.
        if ('image/svg' === $mimetype) {
            return 'image/svg+xml';
        }

        return $mimetype;
    }
}
