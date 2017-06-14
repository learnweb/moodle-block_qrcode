<?php

if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
    header('Cache-Control: max-age=10');
    header('Pragma: ');
} else { //normal http - prevent caching at all cost
    header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    header('Pragma: no-cache');
}
header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
header("Content-Type: image/png");
header("Content-Disposition: attachment; filename=Kurs-1.png");

$im = imagecreatefromstring(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAGMAAABjAQMAAAC19SzWAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAA4klEQVQ4jdXTsQ3EIAwAQL8o6GCBSKxBx0qZIE8WICulY41ILPDfUUT4LV5K0hDTxgXKFRhjHICnhEacV+MdFk4KxBKxWPrg5NKMIlgMHaJ9vk/epi4Bpd/Hs7Km6H7BpnDetqka23RtXEMaE2aDcRhZZdBxUxJYvVaDqyhunzjpPLyzmGkrJ5C4RIM5FU6Kcjuq5V/1vUSAVOwwsbJb7ceuWMnkwSxIK6N6CKVHVvS2S6ZzjpduiuZljinArljRtK5I8elQcOJ7dPdeMExSBFb0/2V4x6Oypupco79OeUPPiB9fQVtKN/5btwAAAABJRU5ErkJggg=='));
imagepng($im);

exit();
