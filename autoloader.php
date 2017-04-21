<?php
spl_autoload_register(function($class) {
    if (file_exists('./libs/'.$class.'.php')) {
        require './libs/'.$class.'.php';
    }
    else {
        trigger_error('Class not found', E_USER_WARNING);
    }
});