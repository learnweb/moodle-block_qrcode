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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot.'/repository/lib.php');

/**
 * Instance configuration for the qrcode block.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_edit_form extends block_edit_form {

    /**
     * The definition of the fields to use.
     *
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition($mform) {
        //Accepted types hängen davon ab...
//      $mform->addElement('filemanager', 'logopng', get_string('file_png', 'block_qrcode'), null,
//          array('subdirs' => 0,
//              'maxbytes' => 2000000,
//              'areamaxbytes' => 2000000,
//              'maxfiles' => 1,
//              'accepted_types' => array('.png'),
//           'return_types' => FILE_INTERNAL | FILE_EXTERNAL));
//
//      $mform->addElement('filemanager', 'logosvg', get_string('file_svg', 'block_qrcode'), null,
//          array('subdirs' => 0,
//              'maxbytes' => 2000000,
//              'areamaxbytes' => 2000000,
//              'maxfiles' => 1,
//              'accepted_types' => array('.svg'),
//           'return_types' => FILE_INTERNAL | FILE_EXTERNAL));

      //neuer file eintrag auch, wenn nichts hochgeladen wurden?!


        $mform->addElement('filepicker', 'logopng', get_string('file_png', 'block_qrcode'), null,
            array('maxbytes' => 2000000, 'accepted_types' => array('.png')));


        $mform->addElement('filepicker', 'logosvg', get_string('file_svg', 'block_qrcode'), null,
            array('maxbytes' => 2000000, 'accepted_types' => array('.svg')));


        $png = $this->get_file_content('logopng');
        $svg = $this->get_file_content('logosvg');

        var_dump($png);
        var_dump($svg);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        var_dump($data);
        var_dump($files);
        return array();
    }
}