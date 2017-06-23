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

defined('MOODLE_INTERNAL') || die;

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
     * @param $image QR code
     * @return string html-string
     */
    public function display_image($image) {
        return html_writer::img('data:image/png;base64,'.base64_encode($image), get_string('img_tag_alt', 'block_qrcode'));
    }

    /**
     * Generates link to download the QR code
     * @param $image QR code
     * @param $id course id
     * @return string button
     */
    public function display_download_link($image, $id) {
        $button = new single_button(new moodle_url('/blocks/qrcode/download.php',
            array('image' => $image, 'courseid' => $id)),
            get_string('button', 'block_qrcode'));
        return $this->render($button);
    }
}