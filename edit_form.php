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

defined('MOODLE_INTERNAL') || die();

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
    }
}