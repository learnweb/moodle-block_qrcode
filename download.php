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

$courseid = required_param('courseid', PARAM_INT);
$download = required_param('download', PARAM_BOOL);
$format = required_param('format', PARAM_TEXT);
$instanceid = required_param('instance', PARAM_INT);
$size = optional_param('size', 150, PARAM_INT);

if ($download) {
    require_capability('block/qrcode:download', context_course::instance($courseid));
}

$outputimg = new block_qrcode\output_image($format, $size, $courseid, $instanceid);
$outputimg->output_image($download);
