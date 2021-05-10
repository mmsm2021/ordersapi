<?php

/** State of request */
$verified = true;

/** Verified requests are handled */
if($verified) {
    include(dirname(__FILE__)."/../../DataHandlers/OrderCreate.php");
} else {
    /** request denied */
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden!';
}
