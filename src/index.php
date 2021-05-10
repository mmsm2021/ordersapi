<?php

#phpinfo();

require_once 'bootstrap.php';

/** verification of correct uri */
if(strpos($_SERVER['REQUEST_URI'], '/api/orders/') !== false)
{
        /** sorting based on request type */
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            include 'Rest/Delete/Delete.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            include 'Rest/Get/Get.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
            include 'Rest/Head/Head.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            include 'Rest/Options/Options.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include 'Rest/Post/Post.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            include 'Rest/Put/Put.php';
        } else {
            /** Request denied */
            header('HTTP/1.0 405 Forbidden');
            echo 'Not supported!';
        }
    } else {
        /** Request denied */
        header('HTTP/1.0 403 Forbidden');
        echo 'Forbidden!';
    }
    