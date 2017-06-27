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
 * This file contains the script for downloading a QR code.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();
$file = required_param('file', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);

// Output file headers to initialise the download of the file.
if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
    header('Cache-Control: max-age=10');
    header('Pragma: ');
} else { // Normal http - prevent caching at all cost.
    header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    header('Pragma: no-cache');
}
header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename=' . get_string('filename', 'block_qrcode') . '-' . $courseid . '.png');

// Outputs (& downloads) the image file.
readfile($file);
exit();
