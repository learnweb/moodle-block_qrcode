<?php
require_once 'phpqrcode/qrlib.php';
require_once 'phpqrcode/qrconfig.php';
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
    public function get_content() {
        if($this->content !== null) {
            return $this->content;
        }

        global $COURSE, $PAGE, $CFG;

        $url = course_get_url($COURSE);

        $this->content = new stdClass;
        $this->content->text = '';


        $cache = cache::make('block_qrcode', 'qrcodes');

        //checks if QR code already exists
        if(!$cache->get($COURSE->fullname)) {
            //creates QR code
            ob_implicit_flush(false);
            ob_start();
            QRcode::png($url->out());
            $image = ob_get_contents();
            ob_end_clean();

            $cache->set($COURSE->fullname, $image);
            $this->content->text .= '<img src="data:image/png;base64,'.base64_encode($image).'"/';

        }
        else {
            $image = $cache->get($COURSE->fullname);
            $this->content->text .= '<img src="data:image/png;base64,'.base64_encode($image).'"/';
        }


        return $this->content;


        /*      //Will be created every time?! -> save filename in database?
              $filepath = $CFG->localcachedir;

              //wird augerufen, wenn content aktualiisert -> nur beim hinzufügen einmal ausführen
              if(!is_dir($CFG->localcachedir.'/qrcodes2'))
                  mkdir($CFG->localcachedir.'/qrcodes2');
              //create new folder if not exists
              $filepath .= '/qrcodes2/';
              //creates the QR code
              $filename = 'qrcode_';
              $filename .= $COURSE->fullname;
              $filename .= '.png';
              QRcode::png($url->out(), $filepath.$filename);

              $layout = new qrcode_form();

              $this->content->text .= $layout->render();

              $browser = get_file_browser();
              $context = context_system::instance();

              $fs = get_file_storage();
       //       make_temp_directory('qrcodes');
              $fileinfo = array('contextid' => $context->id,
                  'component' => 'blocks_qrcode',
                  'filearea' => 'qrcode',
                  'itemid' => 2,
                  'filepath' => '/',
                  'filename' => $filename);

              $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                  $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
              if(!$file)
                  $file = $fs->create_file_from_pathname($fileinfo, $filepath.$filename);
              $pictureurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                  null, $file->get_filepath(), $file->get_filename());


              $files = $fs->get_area_files($file->get_contextid(), 'blocks_qrcode', 'qrcode');

              $this->content->text .= '<img src="data:image/png;base64,'.base64_encode($file->get_content()).'"/';
            */
    }

    /**
     * @return array
     */
    public function applicable_formats() {
        return array('course-view' => true, 'mod' => false, 'my' => false);
    }
}