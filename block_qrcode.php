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

require_once 'phpqrcode/qrlib.php';
require_once 'phpqrcode/qrconfig.php';

/**
 * Class block_qrcode
 *
 * Displays a QR code with a link to the course page.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qrcode extends block_base
{
    /**
     * Initializes the block.
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_qrcode');
    }

    /**
     * Returns the content object.
     *
     * @return  object $this->content
     */
    public function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }

        // Record the current course and page
        global $COURSE, $PAGE;

        $url = course_get_url($COURSE);

        $this->content = new stdClass;
        $this->content->text = '';

        // Gets the cache object
        $cache = cache::make('block_qrcode', 'qrcodes');

        //checks if QR code already exists
        if (!$cache->get($COURSE->id)) {
            // creates the QR code
            ob_implicit_flush(false);
            ob_start();
            QRcode::png($url->out());
            $image = ob_get_contents();
            ob_end_clean();

            // saves the QR code
            $cache->set($COURSE->id, $image);
        } else {
            // loads the QR code
            $image = $cache->get($COURSE->id);
        }

        // displays the block
        $renderer = $PAGE->get_renderer('block_qrcode');
        $this->content->text .= $renderer->display_image($image);
        $this->content->text .= '<br>';
        $this->content->text .= $renderer->display_download_link(base64_encode($image), $COURSE->id);

        return $this->content;


    }

    /**
     * The block is only available at course-view pages
     *
     * @return array of applicable formats
     */
    public function applicable_formats()
    {
        return array('course-view' => true, 'mod' => false, 'my' => false);
    }

    /**
     * If a course is deleted, the QR code is also deleted.
     * @param \core\event\course_deleted $event
     */
    public static function observe_course_deleted(\core\event\course_deleted $event)
    {
        $cache = cache::make('block_qrcode', 'qrcodes');

        //QR code exists
        if ($cache->get($event->courseid)) {
            $cache->delete($event->courseid);
        }
    }
}