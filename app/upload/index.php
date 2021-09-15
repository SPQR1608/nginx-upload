<?php
if (!empty($_REQUEST['X-Progress-ID'])) {
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'].'/upload/tmp/'.$_REQUEST['X-Progress-ID'],
        json_encode($_REQUEST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}