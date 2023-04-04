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
 * Settings for the qrcode block.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('block_qrcode/use_logo',
            get_string('use_logo', 'block_qrcode'),
            get_string('use_logo_help', 'block_qrcode'),
            '1'
    ));

    $settings->add(new admin_setting_configstoredfile('block_qrcode/logofile_png',
            get_string('logofile_png', 'block_qrcode'),
            '', 'logo_png', 0, ['accepted_types' => '.png']
    ));

    $settings->add(new admin_setting_configstoredfile('block_qrcode/logofile_svg',
            get_string('logofile_svg', 'block_qrcode'),
            '', 'logo_svg', 0, ['accepted_types' => '.svg']
    ));

    $settings->add(new admin_setting_configcheckbox('block_qrcode/allow_customlogo',
        get_string('allow_customlogo', 'block_qrcode'),
        get_string('allow_customlogo_help', 'block_qrcode'),
        '0'
    ));

}
