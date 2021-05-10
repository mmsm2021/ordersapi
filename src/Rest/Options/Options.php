<?php

/** State of request */
$verified = true;

/** Verified requests are handled */
if($verified) {
    http_response_code(204);
    header('Allow: GET, POST, PUT, DELETE, HEAD, OPTIONS');
} else {
    /** request denied */
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden!';
}