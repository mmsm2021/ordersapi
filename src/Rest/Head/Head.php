<?php

$verified = true;

if($verified) {
    # This echo should not be displayed by client, as body on HEAD requests should not occur, and thus is to be ignored by client 
    $query_pos = strrpos($_SERVER['REQUEST_URI'],"/");
    $query = substr($_SERVER['REQUEST_URI'], $query_pos+1);
    echo $query;
} else {
    header('HTTP/1.0 403 Forbidden');
}