<?php
require_once('../../config.php');
require_login();
$image = required_param('image', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);

if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
    header('Cache-Control: max-age=10');
    header('Pragma: ');
} else { //normal http - prevent caching at all cost
    header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    header('Pragma: no-cache');
}
header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
header("Content-Type: image/png");
header("Content-Disposition: attachment; filename=Kurs-".$courseid.".png");

$im = imagecreatefromstring(base64_decode($image));
imagepng($im);
exit();
