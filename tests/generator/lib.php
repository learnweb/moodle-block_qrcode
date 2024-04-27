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
 * block_qrcode data generator
 *
 * @package    block_qrcode
 * @category   test
 * @copyright  2017 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Online users block data generator class
 *
 * @package    block_qrcode
 * @category   test
 * @copyright  2017 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_generator extends testing_block_generator {

    /**
     * Creates a course.
     * @return array
     */
    public function create_course() {
        $generator = advanced_testcase::getDataGenerator();
        $data = [];

        // Create a course.
        $course = $generator->create_course();
        $data['course'] = $course;

        return $data;
    }
}
