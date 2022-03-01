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

namespace block_qrcode;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Class block_qrcode_form
 *
 * Moodle form for qrcode block.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_form extends moodleform {

    /**
     * Form definition.
     * Displays dropdown menus (format & size) and an action button (Download).
     */
    public function definition() {
        $mform = $this->_form;

        // Select file type.
        $selectf = $mform->addElement(
            'select',
            'format',
            get_string('formats', 'block_qrcode'),
            array(1 => 'svg', 2 => 'png'),
            array('id' => 'slc_format'));
        $selectf->setSelected($this->_customdata['format']);

        // Select image size.
        $selects = $mform->addElement(
            'select',
            'size',
            get_string('sizes', 'block_qrcode'),
            array(150 => '150px', 300 => '300px'),
            array('id' => 'slc_size', 'disabled' => true));
        $selects->setSelected($this->_customdata['size']);
        $mform->disabledIf('size', 'format', 'eq', 1);

        $this->add_action_buttons(false, get_string('button', 'block_qrcode'));
    }
}
