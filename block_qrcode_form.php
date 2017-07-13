<?php

require_once($CFG->libdir.'/formslib.php');

class qrcode_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $selectF = $mform->addElement('select', 'format', get_string('formats', 'block_qrcode'), array(1=>'png', 2=>'svg'));
        $selectF->setSelected('png');
        $selectS = $mform->addElement('select', 'size', get_string('sizes', 'block_qrcode'),array(100=>'100px', 300=>'300px'));
        $selectS->setSelected('100px');

        $this->add_action_buttons(false, get_string('button', 'block_qrcode'));
    }
}