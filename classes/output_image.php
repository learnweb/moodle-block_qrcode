<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains a class that provides functions for downloading/displaying a QR code.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_qrcode;
use Box\Spout\Reader\Wrapper\SimpleXMLElement;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/qrcode/thirdparty/vendor/autoload.php');
/**
 * Class output_image
 *
 * Downloads or displays QR code.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_image {
    protected $url; // QR code points to this url.
    protected $fullname;
    protected $file; // QR code is saved in this file.
    protected $format;
    protected $size;

    /**
     * output_image constructor.
     * @param $url of the course (content of QR code)
     * @param $courseid
     * @param $file path to QR code
     */
    public function __construct($url, $fullname, $file, $format, $size) {
        $this->url = $url;
        $this->fullname = $fullname;
        $this->file = $file;
        $this->format = $format;
        $this->size = $size;
    }

    /**
     * Creates the QR code if it doesn't exist.
     */
    public function create_image() {
        global $CFG;
        // Checks if QR code already exists.
        if (!file_exists($this->file)) {
            // Checks if directory already exists.
            if (!file_exists(dirname($this->file))) {
                // Creates new directory.
                mkdir(dirname($this->file), $CFG->directorypermissions, true);
            }

            // Creates the QR code.
            $qrcode = new QrCode($this->url);
            $qrcode->setSize($this->size);

            if($this->format == 1)
                $qrcode->setWriterByName('png');
            else
                $qrcode->setWriterByName('svg');

            // Set advanced options.
            $qrcode
                ->setMargin(10)
                ->setEncoding('UTF-8')
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
                ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
                ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255])
                ->setLogoPath($CFG->dirroot.'/blocks/qrcode/moodle-logo.png')
                ->setLogoWidth($this->size/3)
            ;

            $qrcode->writeFile($this->file);
        }
    }

    /**
     * Outputs file headers to initialise the download of the file / display the file.
     * @param $download true, if the QR code should be downloaded
     */
    protected function send_headers($download) {
        // Caches file for 1 month.
        header('Cache-Control: public, max-age:2628000');

        if($this->format == 1)
            header('Content-Type: image/png');
        else
            header('Content-Type: image/svg+xml');


        // Checks if the image is downloaded or displayed.
        if ($download) {
            // Output file header to initialise the download of the file.
            // filename: QR Code - fullname -> Größe noch benennen??
            if($this->format == 1)
                header('Content-Disposition: attachment; filename="QR Code-'.$this->fullname.'.png"');
            else
                header('Content-Disposition: attachment; filename="QR Code-'.$this->fullname.'.svg"');
        }
    }

    /**
     * Outputs (downloads or displays) the QR code.
     * @param $download true, if the QR code should be downloaded
     */
    public function output_image($download) {
        $this->create_image();
        $this->send_headers($download);
        readfile($this->file);
        exit();
    }

    private function modify_svg($svgqrcode) {
        $svg = new SimpleXMLElement($svgqrcode);
    }
}