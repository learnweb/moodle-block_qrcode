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
 * Qr code library code.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_qrcode;

use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * Class admin_form
 *
 * Moodle form for settings site.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_form extends moodleform {

    /**
     * Creates the form.
     */
    protected function definition() {
        $mform = $this->_form;

        // Checkbox.
        $mform->addElement('advcheckbox', 'use_logo', get_string('use_logo', 'block_qrcode'));
        $mform->setType('use_logo', PARAM_BOOL);
        $mform->addHelpButton('use_logo', 'use_logo', 'block_qrcode');

        // Filemanager for .png logo.
        $mform->addElement('filemanager', 'logo_png', get_string('logofile_png', 'block_qrcode'), null,
            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => '.png'));

        // Filemanager for .svg logo.
        $mform->addElement('filemanager', 'logo_svg', get_string('logofile_svg', 'block_qrcode'), null,
            array('subdirs' => 0, 'maxbytes' => 1000000, 'maxfiles' => 1, 'accepted_types' => '.svg'));

        $mform->addElement('submit', 'submitbutton', get_string('submit', 'block_qrcode'));
    }
}