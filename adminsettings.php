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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_login();

require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('block_qrcode');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings', 'block_qrcode'));

$mform = new block_qrcode\admin_form();

// Form is submitted.
if ($data = $mform->get_data()) {
    // Set logo config.
    $oldvalue = get_config('block_qrcode', 'use_logo');
    $oldvalue = ($oldvalue === false) ? null : $oldvalue;
    $value = $data->use_logo;

    if ($oldvalue !== $value) {
        // Store change.
        set_config('use_logo', $value, 'block_qrcode');
    }

    $contextid = context_system::instance()->id;

    // Save png logo.
    if ($draftitemid = file_get_submitted_draft_itemid('logo_png')) {
        file_save_draft_area_files($draftitemid, $contextid, 'block_qrcode', 'logo_png', 0,
            array('subdirs' => false, 'maxfiles' => 1));

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'block_qrcode', 'logo_png', 0, 'sortorder,filepath,filename', false);

        $filepath = '';
        if ($files) {
            /** @var stored_file $file */
            $file = reset($files);
            $filepath = $file->get_filepath() . $file->get_filename();
        }
        set_config('logofile_png', $filepath, 'block_qrcode');
    }

    // Save svg logo.
    if ($draftitemid = file_get_submitted_draft_itemid('logo_svg')) {
        file_save_draft_area_files(
            $draftitemid,
            $contextid,
            'block_qrcode',
            'logo_svg',
            0,
            array('subdirs' => false, 'maxfiles' => 1));

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'block_qrcode', 'logo_svg', 0, 'sortorder,filepath,filename', false);

        $filepath = '';
        if ($files) {
            /** @var stored_file $file */
            $file = reset($files);
            $filepath = $file->get_filepath() . $file->get_filename();
        }
        set_config('logofile_svg', $filepath, 'block_qrcode');
    }
}

if (empty($entry->id)) {
    $entry = new stdClass;
    $entry->id = 0;
}

// Load existing files in draft area.
$draftitemid = file_get_submitted_draft_itemid('logo_png');
file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_qrcode', 'logo_png',
    $entry->id, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));

$entry->logo_png = $draftitemid;

$draftitemid = file_get_submitted_draft_itemid('logo_svg');
file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_qrcode', 'logo_svg',
    $entry->id, array('subdirs' => 0, 'maxbytes' => 1000000, 'maxfiles' => 1));

$entry->logo_svg = $draftitemid;

$entry->use_logo = get_config('block_qrcode', 'use_logo');

$mform->set_data($entry);
$mform->display();


echo $OUTPUT->footer();

