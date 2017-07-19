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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class qrcode_form extends moodleform {

    /**
     * Form definition.
     * Displays dropdown menus (format & size) and an action button (Download).
     */
    public function definition() {
        $mform = $this->_form;
        $selectF = $mform->addElement('select', 'format', get_string('formats', 'block_qrcode'), array(1 => 'png', 2 => 'svg'));
        $selectF->setSelected($this->_customdata['format']);
        $selectS = $mform->addElement('select', 'size', get_string('sizes', 'block_qrcode'), array(100 => '100px', 300 => '300px'));
        $selectS->setSelected($this->_customdata['size']);

        $this->add_action_buttons(false, get_string('button', 'block_qrcode'));
    }
}