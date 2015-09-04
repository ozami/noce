<?php
use \Noce\Debug;

function d($data)
{
    if (Debug::$_log_lv < Debug::DEBUG && Debug::$_display_lv < Debug::DEBUG) { // performance boost
        return $data;
    }
    return Debug::d($data);
}
