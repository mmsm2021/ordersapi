<?php

$verified = true;

if($verified) {
    http_response_code(204);
    header('Allow: GET, POST, PUT, DELETE, HEAD, OPTIONS');
} else {
    header('HTTP/1.0 403 Forbidden');
}