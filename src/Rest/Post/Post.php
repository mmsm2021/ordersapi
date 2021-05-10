<?php

$verified = true;

if($verified) {
    include(dirname(__FILE__)."/../../DataHandlers/OrderCreate.php");
} else {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden!';
}
