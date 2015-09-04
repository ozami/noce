<?php
namespace Noce;

class Input_Date extends Input_String
{
    public $mb = "as";
    public $noSpace = true;

    public function doValidate($value)
    {
        $time = strtotime($value);
        if ($time !== Time::truncate($time, "mday")) {
            return "err_invalid_date";
        }
        return parent::doValidate($value);
    }
}
