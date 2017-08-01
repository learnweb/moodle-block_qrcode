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
 * This file contains the script for downloading/displaying a QR code.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php'); // To include $CFG.
require_login();

$url = required_param('url', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);
$fullname = required_param('fullname', PARAM_TEXT);
$download = required_param('download', PARAM_BOOL);
$format = required_param('format', PARAM_TEXT);
$size = required_param('size', PARAM_INT);
$contextid = required_param('contextid', PARAM_INT);

$file = $CFG->localcachedir . '/block_qrcode/course-' .
    $courseid . '-' . $size . '-' .
    get_config('block_qrcode', 'logo'); // File path without file ending.

if ($format == 1) {
    $file .= '.svg';
} else {
    $file .= '.png';
}
$outputimg = new block_qrcode\output_image($url, $fullname, $file, $format, $size, $contextid);
$outputimg->output_image($download);