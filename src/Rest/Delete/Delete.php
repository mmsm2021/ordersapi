<?php

$verified = true;

if($verified) {
    $query_pos = strrpos($_SERVER['REQUEST_URI'],"/");
    $query = substr($_SERVER['REQUEST_URI'], $query_pos+1);
    echo $query;
} else {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden!';
}
