<?php


require_once($CFG->libdir.'/formslib.php');
/**
 * Created by IntelliJ IDEA.
 * User: tamara
 * Date: 08.06.17
 * Time: 10:44
 */
class qrcode_form extends moodleform {

    public function  definition()
    {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('button', 'intro', 'Download');
      //  $mform->addElement('img')
    //    $mform->addElement('filepicker', )
    }
}