<?php

$verified = true;

if($verified) {
    $json = file_get_contents('php://input');
    echo $json;
} else {
    header('HTTP/1.0 403 Forbidden');
}
