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

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use DOMDocument;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/qrcode/thirdparty/vendor/autoload.php');
require_once($CFG->dirroot . '/course/lib.php');

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
    /**
     * QR code is saved in this file.
     * @var string
     */
    protected $file;

    /**
     * Output file type.
     * 1 - svg, 2 - png
     * @var int
     */
    protected $format;

    /**
     * Size of qrcode (downloaded image).
     * Only for png.
     * @var int
     */
    protected $size;

    /**
     * @var bool Whether to use a logo in the qrcode.
     */
    protected $uselogo;

    /**
     * @var \stored_file Stored file for custom logo.
     */
    protected $logofile;

    /**
     * Course for which the qrcode is created.
     * @var \stdClass
     */
    protected $course;

    /**
     * Block instanceID.
     * @var int
     */
    protected $instanceid;
    /**
     * output_image constructor.
     * @param int $format file type
     * @param int $size image size
     * @param int $courseid course for which the qrcode is created
     * @param int $instanceid
     */
    public function __construct($format, $size, $courseid, $instanceid) {
        global $CFG, $DB;
        $this->format = $format;
        $this->size = (int)$size;
        $this->course = get_course($courseid);
        $this->instanceid = $instanceid;
        $file = $CFG->localcachedir . '/block_qrcode/course-' .
            (int)$courseid . '-' . $this->size; // Set file path.

        $instance = $DB->get_record('block_instances', ['id' => $instanceid], '*', MUST_EXIST);
        $block = block_instance('qrcode', $instance);

        if (is_null($block->config)) {
            $block->config = new \StdClass();
            $block->config->usedefault = true;
        }

        $this->uselogo = ($block->config->usedefault && get_config('block_qrcode', 'use_logo') == 1) ||
                (!$block->config->usedefault && $block->config->instc_uselogo);

        // Set custom logo path.
        if ($this->uselogo) {
            $this->logofile = $this->get_logo();

            if ($this->logofile) {
                $file .= '-' . $this->logofile->get_contenthash();
            } else {
                $file .= '-default';
            }
        } else {
            $file .= '-0';
        }

        // Add file ending.
        if ($format == 1) {
            $file .= '.svg';
        } else {
            $file .= '.png';
        }

        $this->file = $file;
    }

    /**
     * Creates the QR code if it doesn't exist.
     */
    public function create_image() {
        global $CFG;
        // Checks if QR code already exists.
        if (file_exists($this->file)) {
            // File exists in cache.
            return;
        }

        // Checks if directory already exists.
        if (!file_exists(dirname($this->file))) {
            // Creates new directory.
            mkdir(dirname($this->file), $CFG->directorypermissions, true);
        }

        // Creates the QR code.
        // Check if a custom host should be used.
        $customwwwroot = get_config('block_qrcode', 'custom_wwwroot');
        if (get_config('block_qrcode', 'use_customwwwroot') == '1' &&  $customwwwroot != '') {
            $url = $customwwwroot . $this->course->id . '&utm_source=block_qrcode';
        } else {
            $url = course_get_url($this->course)->out(false, ['utm_source' => 'block_qrcode']);
        }
        $qrcode = new QrCode($url);
        $qrcode->setSize($this->size);

        // Set advanced options.
        $qrcode->setMargin(10);
        $qrcode->setEncoding(new Encoding('UTF-8'));
        $qrcode->setErrorCorrectionLevel(new ErrorCorrectionLevel\ErrorCorrectionLevelHigh());
        $qrcode->setForegroundColor(new Color(0, 0, 0));
        $qrcode->setBackgroundColor(new Color(255, 255, 255));

        // Png format.
        $logo = null;
        if ($this->format == 2) {
            $writer = new PngWriter();
            if ($this->uselogo) {
                if ($this->logofile) {
                    $logopath = $this->logofile->copy_content_to_temp();
                } else {
                    $logopath = $CFG->dirroot . '/blocks/qrcode/pix/moodlelogo.png';
                }
                $logo = new Logo(
                    $logopath,
                    intval($this->size * 0.4),
                    null,
                    false
                );
            }
        } else {
            $writer = new SvgWriter();
            if ($this->uselogo) {
                $logopath = $this->logofile ? $this->logofile->copy_content_to_temp()
                        : $CFG->dirroot . '/blocks/qrcode/pix/moodlelogo.svg';
                $logo = new Logo(
                    $logopath,
                    intval($this->size * 0.4),
                    intval($this->size * 0.4 / $this->get_logo_aspect_ratio($logopath)),
                    false
                );
            }
        }
        $result = $writer->write($qrcode, $logo);
        $result->saveToFile($this->file);
    }

    /**
     * Outputs file headers to initialise the download of the file / display the file.
     * @param bool $download true, if the QR code should be downloaded
     */
    protected function send_headers($download) {
        // Caches file for 1 month.
        header('Cache-Control: public, max-age:2628000');

        if ($this->format == 2) {
            header('Content-Type: image/png');
        } else {
            header('Content-Type: image/svg+xml');
        }

        // Checks if the image is downloaded or displayed.
        if ($download) {
            // Output file header to initialise the download of the file.
            // filename: QR Code-%s.(svg|png), where %s is derived from the course's fullname.
            if ($this->format == 2) {
                header('Content-Disposition: attachment; filename="QR Code-' .
                    clean_param($this->course->fullname, PARAM_FILE) . '.png"');
            } else {
                header('Content-Disposition: attachment; filename="QR Code-' .
                    clean_param($this->course->fullname, PARAM_FILE) . '.svg"');
            }
        }
    }

    /**
     * Outputs (downloads or displays) the QR code.
     * @param bool $download true, if the QR code should be downloaded
     */
    public function output_image($download) {
        $this->create_image();
        $this->send_headers($download);
        readfile($this->file);
    }

    /**
     * Gets the aspect ratio (width / height) of a svg.
     * @param string $logopath path of the svg.
     * @return float the aspect ratio
     */
    protected function get_logo_aspect_ratio($logopath) {
        // Loads logo.
        $xmllogo = new DOMDocument();
        $xmllogo->load($logopath);

        $viewbox = $xmllogo->documentElement->getAttribute('viewBox');
        $viewboxbounds = explode(' ', $viewbox);
        $logowidth = $viewboxbounds[2];
        $logoheight = $viewboxbounds[3];
        return $logowidth / $logoheight;
    }

    /**
     * Returns the stored file of the customlogo, if possible.
     * If not, returns the stored file of the default logo.
     * @return \stored_file|null stored file
     */
    public function get_logo() {

        $fssvg = get_file_storage();
        $fspng = get_file_storage();

        // Get File.
        $filessvg = $fssvg->get_area_files(
            \context_block::instance($this->instanceid)->id,
            'block_qrcode',
            'customlogosvg',
            0,
            'sortorder',
            false
        );
        $filespng = $fspng->get_area_files(
            \context_block::instance($this->instanceid)->id,
            'block_qrcode',
            'customlogopng',
            0,
            'sortorder',
            false
        );

        if ($this->format == 1) {
            if (count($filessvg) == 1 && get_config('block_qrcode', 'allow_customlogo') == 1) {
                $filesvg = reset($filessvg);
                return $filesvg;
            } else {
                $filearea = 'logo_svg';
                $completepath = get_config('block_qrcode', 'logofile_svg');

                $fs = get_file_storage();
                $filepath = pathinfo($completepath, PATHINFO_DIRNAME);
                $filename = pathinfo($completepath, PATHINFO_BASENAME);
                $file = $fs->get_file(
                    \context_system::instance()->id,
                    'block_qrcode',
                    $filearea,
                    0,
                    $filepath,
                    $filename
                );
                if ($file) {
                    return $file;
                }
            }
        } else {
            if (count($filespng) == 1 && get_config('block_qrcode', 'allow_customlogo') == 1) {
                $filepng = reset($filespng);
                return $filepng;
            } else {
                $filearea = 'logo_png';
                $completepath = get_config('block_qrcode', 'logofile_png');

                $fs = get_file_storage();
                $filepath = pathinfo($completepath, PATHINFO_DIRNAME);
                $filename = pathinfo($completepath, PATHINFO_BASENAME);
                $file = $fs->get_file(
                    \context_system::instance()->id,
                    'block_qrcode',
                    $filearea,
                    0,
                    $filepath,
                    $filename
                );
                if ($file) {
                    return $file;
                }
            }
        }
        return null;
    }
}
