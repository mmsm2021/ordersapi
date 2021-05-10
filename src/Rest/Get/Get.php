<?php

/** State of request */
$verified = true;

/** Verified requests are handled */
if($verified) {
    /** Detection: is one or more "last" documents requested, or is a specific document requested */
    if(strpos($_SERVER['REQUEST_URI'], '/api/orders/last/') !== false)
    {
        include(dirname(__FILE__)."/../../DataHandlers/OrderIdFetcher.php");
    } else {
        include(dirname(__FILE__)."/../../DataHandlers/OrderRead.php");
    }
} else {
    /** request denied */
    header('HTTP/1.0 403 Forbidden');
}
