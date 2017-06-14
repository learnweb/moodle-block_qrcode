<?php
require_once 'phpqrcode/qrlib.php';
require_once 'phpqrcode/qrconfig.php';
require_once 'block_qrcode_form.php';
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
            $this->content->text .= '<img src="data:image/png;base64,' . base64_encode($image) . '"/>';

        } else {
            $image = $cache->get($COURSE->id);
            $this->content->text .= '<img src="data:image/png;base64,' . base64_encode($image) . '"/';
        }

        $layout = new block_qrcode_form();
        $this->content->text .= $layout->render();
        

        //$this->download_image($image);
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

    private function download_image($image)
    {

        if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
            header('Cache-Control: max-age=10');
            header('Pragma: ');
        } else { //normal http - prevent caching at all cost
            header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
        }
        header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header("Content-Type: image/png");
        header("Content-Disposition: attachment; filename=Kurs-1.png");

        $png = imagecreatefromstring(base64_decode(base64_encode($image)));
        imagepng($png);
        exit();
    }
}