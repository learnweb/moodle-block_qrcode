<?php

defined('MOODLE_INTERNAL') || die;

class block_qrcode_renderer extends plugin_renderer_base {
    public function display_image($image) {
        return html_writer::img('data:image/png;base64,'.base64_encode($image), 'QR code');
    }

    public function display_download_link($image, $id) {
        global $CFG, $PAGE, $OUTPUT;
        $button = new single_button(new moodle_url('/blocks/qrcode/download.php', array('image' => $image, 'courseid' => $id)), "Download");
        return $this->render($button);
    }
}