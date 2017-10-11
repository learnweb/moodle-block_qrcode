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
 * PHPUnit cron job testcase
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * PHPUnit cron job testcase
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_cron_testcase extends advanced_testcase {

    public function test_cron_job() {

    }

    private function set_up() {
        global $CFG;
        // Create image.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();

        set_config('use_logo', 0, 'block_qrcode');

        $size = 150;
        $file = $CFG->localcachedir.'/block_qrcode/course-'.$course->id. '-'.$size.'-0.svg';
        $outputimg = new block_qrcode\output_image(
            1,
            $size,
            $course->id);
        $outputimg->create_image();
    }
}
