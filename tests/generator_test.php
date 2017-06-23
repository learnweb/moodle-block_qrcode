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
 * PHPUnit data generator tests
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../moodleblock.class.php');
require_once(__DIR__ . '/../block_qrcode.php');


/**
 * PHPUnit data generator testcase
 * @package block_groups
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB, $COURSE;
        $this->resetAfterTest(true);


        $beforeblocks = $DB->count_records('block_instances');
        $generator = $this->getDataGenerator()->get_plugin_generator('block_qrcode');
        $data = $generator->test_create_preparation();
        $this->assertInstanceOf('block_qrcode_generator', $generator);
        $this->assertEquals('qrcode', $generator->get_blockname());
        $instance = $generator->create_instance();
        $this->assertEquals($beforeblocks + 1, $DB->count_records('block_instances'));

        $cache = cache::make('block_qrcode', 'qrcodes');
        $block = new block_qrcode();
        $block->context = context_block::instance($instance->id);
        $block->get_content();
        $this->assertInternalType('string', $cache->get($COURSE->id));
        // image wird nicht erzeugt...
  //      print('/'.$cache->get($COURSE->id));
        $this->assertTrue($cache->delete($COURSE->id));
    }
}
