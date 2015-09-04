<?php
namespace Noce;

class Input_Postal extends Input_String
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
        if (!preg_match("/^[0-9]{3}-[0-9]{4}$/u", $value)) {
            return "err_invalid_postal";
        }
        return parent::doValidate($value);
    }
}
