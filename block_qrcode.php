<?php
require_once 'phpqrcode/qrlib.php';
require_once 'phpqrcode/qrconfig.php';
//include 'download.php';

/**
 * Class block_qrcode
 *
 * Displays a QR code with a link to the course page
 *
 * @copyright 2017 T Gunkel
 */
class block_qrcode extends block_base
{
    /**
     * Initializes the block.
     */
    public function init()
    {
        $this->title = get_string("qrcode", "block_qrcode");
    }

    /**
     * Returns the content object.
     * @return  object $this->content
     */
    public function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }

        global $COURSE, $PAGE, $CFG;

        $url = course_get_url($COURSE);

        $this->content = new stdClass;
        $this->content->text = '';


        $cache = cache::make('block_qrcode', 'qrcodes');

        //checks if QR code already exists
        if (!$cache->get($COURSE->id)) {
            //creates QR code
            ob_implicit_flush(false);
            ob_start();
            QRcode::png($url->out());
            $image = ob_get_contents();
            ob_end_clean();

            $cache->set($COURSE->id, $image);
        } else {
            $image = $cache->get($COURSE->id);
        }

        $renderer = $PAGE->get_renderer('block_qrcode');
        $this->content->text .= $renderer->display_image($image);
        $this->content->text .= '<br>';
        $this->content->text .= $renderer->display_download_link(base64_encode($image), $COURSE->id);
   
        return $this->content;


    }

    /**
     * @return array
     */
    public function applicable_formats()
    {
        return array('course-view' => true, 'mod' => false, 'my' => false);
    }

    /**
     * If the course is deleted, the QR code is also deleted.
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