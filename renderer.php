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
 * This file contains the renderer for the QR code block.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_qrcode_renderer
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_renderer extends plugin_renderer_base {

    /**
     * Returns a QR code as html image.
     * @param int $courseid course id
     * @param int $instanceid instance id of block
     * @return string html-string
     */
    public function display_image($courseid, $instanceid) {
        $link = new moodle_url('/blocks/qrcode/download.php',
            array('courseid' => $courseid,
                'download' => false,
                'format' => 1,
                'size' => 150,
                'instance' => $instanceid));

        return html_writer::img($link, get_string('img_tag_alt', 'block_qrcode'), array('id'  => 'img_qrcode', 'width' => '90%'));
    }

    /**
     * Displays the download section (menus for choosing format & size, download button).     *
     * @param int $courseid course id
     * @param int $instanceid instance id of block
     * @return string html-string
     */
    public function display_download_section($courseid, $instanceid) {
        $download = new moodle_url('/blocks/qrcode/download.php',
            array('courseid' => $courseid,
                'download' => true,
                'instance' => $instanceid));
        $mform = new block_qrcode\block_qrcode_form($download, array('format' => 1, 'size' => 150), 'post',
                '', ['data-double-submit-protection' => 'off']);
        return $mform->render();
    }

}
