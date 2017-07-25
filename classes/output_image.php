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

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;
use DOMDocument;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/qrcode/thirdparty/vendor/autoload.php');

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
    protected $logopath;
    protected $contextid;

    /**
     * output_image constructor.
     * @param $url of the course (content of QR code)
     * @param $courseid
     * @param $file path to QR code
     */
    public function __construct($url, $fullname, $file, $format, $size, $contextid) {
        global $CFG;
        $this->url = $url;
        $this->fullname = $fullname;
        $this->file = $file;
        $this->format = $format;
        $this->size = $size;
        $this->contextid = $contextid;

        // Set logo path.
        if(get_config('block_qrcode', 'logo') == 1) {
            $this->logopath = $this->getLogoPath();
        }
    }

    /**
     * Creates the QR code if it doesn't exist.
     */
    public function create_image() {
        // new name for qrcodes with logos

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

            // Set advanced options.
            $qrcode
                ->setMargin(10)
                ->setEncoding('UTF-8')
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
                ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
                ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255]);

            if ($this->format == 1) {
                // How to handle cache?? admin verändert einstellung, aber bild wird nicht aktualisiert
                if(get_config('block_qrcode', 'logo') == 1) {
                    $qrcode
                        ->setLogoPath($this->logopath)
                        ->setLogoWidth($this->size/2.75);
                }

                $qrcode->setWriterByName('png');
                $qrcode->writeFile($this->file);
            } else {
                $qrcode->setWriterByName('svg');

                if(get_config('block_qrcode', 'logo') == 1) {
                    $qrcodestring = $qrcode->writeString();
                    $newqrcode = $this->modify_svg($qrcodestring);
                    file_put_contents($this->file, $newqrcode);
                }
                else {
                    $qrcode->writeFile($this->file);
                }
            }
        }
    }

    /**
     * Outputs file headers to initialise the download of the file / display the file.
     * @param $download true, if the QR code should be downloaded
     */
    protected function send_headers($download) {
        // Caches file for 1 month.
        header('Cache-Control: public, max-age:2628000');

        if ($this->format == 1)
            header('Content-Type: image/png');
        else
            header('Content-Type: image/svg+xml');


        // Checks if the image is downloaded or displayed.
        if ($download) {
            // Output file header to initialise the download of the file.
            // filename: QR Code - fullname -> Größe noch benennen??
            if ($this->format == 1)
                header('Content-Disposition: attachment; filename="QR Code-' . $this->fullname . '.png"');
            else
                header('Content-Disposition: attachment; filename="QR Code-' . $this->fullname . '.svg"');
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
        $type = pathinfo($this->logopath, PATHINFO_EXTENSION);
        $logoTargetWidth = $this->size / 2.75;


        if($type == 'png') {
            $logo = imagecreatefromstring(file_get_contents($this->logopath));
            $logoWidth = imagesx($logo);
            $logoHeight = imagesy($logo);

            $scale = $logoTargetWidth / $logoWidth;
            $logoTargetHeight = intval($scale * $logoHeight);
        }
        else {
            //get width and height of svg!!
            $logoTargetHeight = $this->size / 5;

        }
        $logoX = $this->size / 1.75;
        $logoY = $this->size / 1.75;

        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($svgqrcode);
        $logoelem = $xmlDoc->createElement('image');
        $logoelem->setAttribute('x', $logoX);
        $logoelem->setAttribute('y', $logoY);
        $logoelem->setAttribute('width', $logoTargetWidth);
        $logoelem->setAttribute('height', $logoTargetHeight);

        $type = pathinfo($this->logopath, PATHINFO_EXTENSION);
        if($type == 'png') {
            $logoelem->setAttribute('xlink:href', 'data:image/png;base64,'. base64_encode(file_get_contents($this->logopath)));
        }
        else {
            $logoelem->setAttribute('xlink:href', 'data:image/svg+xml;base64,'. base64_encode(file_get_contents($this->logopath)));
        }

        $svgelem = $xmlDoc->getElementsByTagName('svg')->item(0);
        $svgelem->appendChild($logoelem);

        return $xmlDoc->saveXML();
    }

    private function getLogoPath() {
        if($this->format == 1) {
            $itemid = 0;
            $filepath = pathinfo(get_config('block_qrcode', 'logofile_png'), PATHINFO_DIRNAME);
            $filename = pathinfo( get_config('block_qrcode', 'logofile_png'),PATHINFO_BASENAME);
        }
        else {
            $itemid = 1;
            $filepath = pathinfo(get_config('block_qrcode', 'logofile_svg'), PATHINFO_DIRNAME);
            $filename = pathinfo( get_config('block_qrcode', 'logofile_svg'),PATHINFO_BASENAME);

        }
        $pathhash = sha1("/$this->contextid/block_qrcode/logo/$itemid".$filepath.$filename);

        var_dump($pathhash);
    }
}