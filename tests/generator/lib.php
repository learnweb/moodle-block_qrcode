<?php

defined('MOODLE_INTERNAL') || die();

class block_qrcode_generator extends testing_block_generator {
    public function test_create_preparation() {
        $generator = advanced_testcase::getDataGenerator();
        $data = array();
        $course1 = $generator->create_course(array('name' => 'Some course'));
        $data['course1'] = $course1;

        return $data;
    }
}