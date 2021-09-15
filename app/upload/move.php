<?php
if (isset($_GET['CHECK_FILE_MOVING']) && !empty($_GET['FULL_SIZE'])) {
    $movedSize = filesize($_GET['CHECK_FILE_MOVING']);

    echo $movedSize * 100 / $_GET['FULL_SIZE'];
}
//3bbe39af0dd10b616c487ca581110c74
if (isset($_GET['MOVE_FILE_TARGET'])) {
    $uploadFileInfo = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . $_GET['MOVE_FILE_TARGET'];
    if (file_exists($uploadFileInfo)) {
        $requestArr = json_decode(file_get_contents($uploadFileInfo), true);
        echo '<pre>';
        print_r($requestArr);
        echo '</pre>';
        /*$start = microtime(true);
        $memory = memory_get_usage();

        foreach ($requestArr['file_path'] as $fkey => $fItemSrc) {
            if(file_exists($fItemSrc)) {
                 if (copy($fItemSrc, "/var/www/downloads/"к.$requestArr['file_name'][$fkey])) {
                     unlink($fItemSrc);
                 }
             }
        }

        echo '<br>===============================================================<br>';
        echo 'Время выполнения скрипта: ' . (microtime(true) - $start) . ' sec.';
        echo '<br>===============================================================<br>';
        $i = 0;
        while (floor($memory / 1024) > 0) {
            $i++;
            $memory /= 1024;
        }

        $name = array('байт', 'КБ', 'МБ');
        echo 'Скушано памяти: ' . round($memory, 2) . ' ' . $name[$i];

        unlink($uploadFileInfo);*/
    }
}