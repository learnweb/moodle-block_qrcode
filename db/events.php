<?php
$observers = array(
    array(
        'eventname' => '\core\event\course_deleted',
        'callback' => 'block_qrcode::observe_course_deleted',
    ),
);