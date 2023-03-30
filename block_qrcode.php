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
 * This file contains a class to build a QR code block.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_qrcode
 *
 * Displays a QR code with a link to the course page.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode extends block_base {
    /**
     * Initializes the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_qrcode');
    }

    /**
     * Returns the content object.
     *
     * @return  object $this->content
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        // Record the current course and page.
        global $COURSE;

        $this->content = new stdClass;
        $this->content->text = '';

        // Displays the block.
        /** @var block_qrcode_renderer $renderer */
        $renderer = $this->page->get_renderer('block_qrcode');
        $qrcode = $renderer->display_image($COURSE->id, $this->instance->id);
        $this->content->text .= $qrcode;

        // Students can't see the download button.
        if (has_capability('block/qrcode:download', $this->context)) {
            $this->content->text .= '<br><br>';
            $this->content->text .= $renderer->display_download_section($COURSE->id, $this->instance->id);
        }

        $this->page->requires->js_call_amd('block_qrcode/fullscreenqrcode', 'init', array($qrcode));

        return $this->content;
    }

    /**
     * The block is only available at course-view pages.
     *
     * @return array of applicable formats
     */
    public function applicable_formats() {
        return array('course-view' => true, 'mod' => false, 'my' => false);
    }

    /**
     * Tells moodle, that the qrcode block has a settings file.
     * @return bool true
     */
    public function has_config() {
        return true;
    }

    /**
     * method for saving custom logos in database
     * @param mixed $data
     * @param  bool $nolongerused
     * @return void
     * @throws coding_exception
     */
    public function instance_config_save($data, $nolongerused = false) {

        if (isset($data->customlogosvg)) {
            // Saves logo from the customlogosvg filearea.
            file_save_draft_area_files(
                $data->customlogosvg,
                $this->context->id,
                'block_qrcode',
                'customlogosvg',
                0,
                ['subdirs' => 0, 'maxfiles' => 1]
            );

        }
        if (isset($data->customlogopng)) {
            // Saves files from the customlogopng filearea.
            file_save_draft_area_files(
                $data->customlogopng,
                $this->context->id,
                'block_qrcode',
                'customlogopng',
                0,
                ['subdirs' => 0, 'maxfiles' => 1]
            );

        }
        return parent::instance_config_save($data, $nolongerused);
    }
}
