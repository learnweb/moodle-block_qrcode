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
 * The class contains a test script for the moodle block qrcode
 *
 * @package    block_qrcode
 * @category test
 * @copyright  2017 T Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Class block_qrcode_testcase
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode_testcase extends advanced_testcase
{

    public function test_qrcode()
    {
        global $CFG;
        $generator = $this->getDataGenerator()->get_plugin_generator('block_qrcode');
        $data = $generator->test_create_preparation();
        $this->resetAfterTest(true);

        $generator->create_instance();

        // Gets the cache object
        $cache = cache::make('block_qrcode', 'qrcodes');

        // gibt false zurück??
        $img = $cache->get($data['course1']->id);

        $this->assertGreaterThanOrEqual(50, $img);

    }

    //auf Cache zurgreifen und checken if null
}