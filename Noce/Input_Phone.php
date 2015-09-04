<?php
namespace Noce;

class Input_Phone extends Input_String
{
    public $mb = "as";
    public $noSpace = true;
    
    public function filter($value)
    {
        $value = parent::filter($value);
        $value = preg_replace("/[ー―‐]/u", "-", $value);
        return $value;
    }

    public function doValidate($value)
    {
        if (!preg_match("/^[0-9]{2,6}-[0-9]{2,6}-[0-9]{2,6}$/u", $value)) {
            return "err_invalid_phone";
        }
        return parent::doValidate($value);
    }
}
