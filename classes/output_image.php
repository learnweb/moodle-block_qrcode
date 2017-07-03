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

// namespace block_qrcode;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../config.php'); // To include $CFG.
global $CFG;
require_once($CFG->dirroot.'/blocks/qrcode/phpqrcode/phpqrcode.php');
require_login();

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
    protected $courseid;
    protected $file; // QR code is saved in this file.

    /**
     * output_image constructor.
     * @param $url of the course (content of QR code)
     * @param $courseid
     * @param $file path to QR code
     */
    public function __construct($url, $courseid, $file) {
        $this->url = $url;
        $this->courseid = $courseid;
        $this->file = $file;
    }

    /**
     * Creates the QR code if it doesn't exists.
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
            QRcode::png($this->url, $this->file);
        }
    }

    /**
     * Outputs file headers to initialise the download of the file / display the file.
     * @param $download true, if the QR code should be downloaded
     */
    protected function send_headers($download) {
        // Caches file for 1 month.
        header('Cache-Control: public, max-age:2628000');
        header('Content-Type: image/png');

        // Checks if the image is downloaded or displayed.
        if ($download) {
            // Output file header to initialise the download of the file.
            header('Content-Disposition: attachment; filename='.get_string('filename', 'block_qrcode').'-'.$this->courseid.'.png');
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
}