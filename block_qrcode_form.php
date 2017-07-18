<?php

require_once($CFG->libdir.'/formslib.php');

class qrcode_form extends moodleform {

    /**
     * Form definition.
     * Displays dropdown menus (format & size) and an action button (Download).
     */
    public function definition() {
        $mform = $this->_form;
        $selectF = $mform->addElement('select', 'format', get_string('formats', 'block_qrcode'), array(1=>'png', 2=>'svg'));
        $selectF->setSelected($this->_customdata['format']);
        $selectS = $mform->addElement('select', 'size', get_string('sizes', 'block_qrcode'),array(100=>'100px', 300=>'300px'));
        $selectS->setSelected($this->_customdata['size']);

        $this->add_action_buttons(false, get_string('button', 'block_qrcode'));
    }
}