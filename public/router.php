<?php

    $version = '22.04.00.000';

    $file = __DIR__ . '/../public' . $_SERVER['REQUEST_URI'];
    $file = str_replace("?version=$version", '', $file);

    if (is_file($file)) {
        header('Content-Type: '.mime_content_type($file));
        $fh = fopen($file, 'r');
        fpassthru($fh);
        fclose($fh);
        return true;
    }

    include(__DIR__ . '/index_symfony.php');