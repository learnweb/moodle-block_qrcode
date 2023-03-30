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
 * This file contains a class to edit a QR code block instance.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_qrcode_form
 *
 * Moodle form for editing a qrcode block.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_edit_form extends block_edit_form {

    /**
     * Form definition
     * @param object $mform moodleform
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('editblock', 'block_qrcode'));

        // Checkbox for using default settings or not.
        $mform->addElement('advcheckbox', 'config_usedefault', get_string('usedefault', 'block_qrcode'));
        $mform->setDefault('config_usedefault', true);
        $mform->setType('config_usedefault', PARAM_BOOL);

        // Checkbox for displaying the logo or not.
        $mform->addElement('advcheckbox', 'config_instc_uselogo', get_string('instc_uselogo', 'block_qrcode'));
        $mform->disabledIf('config_instc_uselogo', 'config_usedefault', 'checked');
        $mform->setDefault('config_instc_uselogo', true);
        $mform->setType('config_instc_uselogo', PARAM_BOOL);

        // If the admin settings don't allow a customlogo, the upload option should be disabled.
        if (get_config('block_qrcode', 'allow_customlogo') == 1) {

            // File Area for Customlogo as svg.
            $mform->addElement('filemanager', 'config_customlogosvg', get_string('customfilesvg', 'block_qrcode'),
                null,
                [
                    'subdirs' => 0,
                    'areamaxbytes' => 10485760,
                    'maxfiles' => 1,
                    'accepted_types' => ['.svg'],
                ]
            );

            $mform->addElement('checkbox', 'uploadpng', get_string('uploadpng', 'block_qrcode'));

            // File Area for Customlogo as png.
            $mform->addElement('filemanager', 'config_customlogopng', get_string('customfilepng', 'block_qrcode'),
                null,
                [
                    'subdirs' => 0,
                    'areamaxbytes' => 10485760,
                    'maxfiles' => 1,
                    'accepted_types' => ['.png'],
                ]
            );

            // Only the svg custom logo is displayed in the Qr code.
            // Therefore, the file area for .png is hidden until the checkbox is checked,
            // in order to discourage uploading only a .png file.
            $mform->hideIf('config_customlogopng', 'uploadpng', 'notchecked');

        }

    }

    /**
     * Copies existing logos into draft areas.
     * @param mixed $defaults
     * @return void
     */
    public function set_data($defaults) {
        $draftitemidsvg = file_get_submitted_draft_itemid('customlogosvg');
        $draftitemidpng = file_get_submitted_draft_itemid('customlogopng');
        file_prepare_draft_area($draftitemidsvg, $defaults->parentcontextid, 'block_qrcode', 'customlogosvg', 0);
        file_prepare_draft_area($draftitemidpng, $defaults->parentcontextid, 'block_qrcode', 'customlogopng', 0);
        $defaults->customlogosvg = $draftitemidsvg;
        $defaults->customlogopng = $draftitemidpng;
        parent::set_data($defaults);
    }
}
