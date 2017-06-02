<?php

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
           // return $this->content;
        }

        global $COURSE;
        if($COURSE !== null) {
            $url = course_get_url($COURSE);
            var_dump($COURSE);

        }
        $this->content = new stdClass;
        $this->content->text = 'Test'.$url->out();
//        $this->content->text = 'QR code with a link to the course page';

        return $this->content;
    }
}