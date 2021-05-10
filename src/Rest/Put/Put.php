<?php

/** State of request */
$verified = true;

/** Verified requests are handled */
if($verified) {
    $json = file_get_contents('php://input');
    echo $json;
} else {
    /** request denied */
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden!';
}
