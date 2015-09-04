<?php
namespace Noce;

class Input_Float extends Input_Numeric
{
    public function doValidate($value)
    {
        if ((string) (float) $value != (string) $value) {
            return "err_not_float";
        }
        return parent::doValidate($value);
    }
}
