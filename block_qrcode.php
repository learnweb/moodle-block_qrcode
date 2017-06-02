<?php

class block_qrcode extends block_base
{
    public function init()
    {
        $this->title = get_string("QR code", "block_qrcode");
    }

}