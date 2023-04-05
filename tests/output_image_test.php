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
 * This file contains PHPUnit tests.
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_qrcode;

/**
 * PHPUnit output image testcase
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_image_test extends \advanced_testcase {
    /**
     * course object
     * @var object
     */
    protected $course;
    /**
     * block instance
     * @var object
     */
    protected $block;

    /**
     * Tests initial setup.
     *
     *  Create a course and a block instance.
     */
    protected function setUp() : void {
        $generator = $this->getDataGenerator()->get_plugin_generator('block_qrcode');
        $this->course = $generator->create_course()['course'];
        $this->block = $generator->create_instance();

        // Confirm we have modified the site and requires reset.
        $this->resetAfterTest(true);
    }

    /**
     * Tests, if the image is created.
     */
    public function test_create_image() {
        global $CFG;

        set_config('use_logo', 0, 'block_qrcode');
        $this->assertEquals(0, get_config('block_qrcode', 'use_logo'));

        $size = 150;
        $file = $CFG->localcachedir.'/block_qrcode/course-'.$this->course->id. '-'.$size.'-0.svg';
        $outputimg = new output_image(
            1,
            $size,
            $this->course->id,
            $this->block->id);
        $outputimg->create_image();
        $this->assertFileExists($file);
    }

    /**
     * Tests, if the QR code is created with the moodle logo if no custom logo was uploaded
     * when no logo is uploaded.
     */
    public function test_no_logo() {
        global $CFG;

        $this->assertEquals('', get_config('block_qrcode', 'logofile_svg'));

        $size = 150;
        $file = $CFG->localcachedir.'/block_qrcode/course-'.$this->course->id. '-'.$size.'-default.svg';
        $outputimg = new output_image(
            1,
            $size,
            $this->course->id,
            $this->block->id);
        $outputimg->create_image();
        $this->assertFileExists($file);
    }

}
