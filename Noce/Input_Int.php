<?php
namespace Noce;

class Input_Int extends Input_Numeric
{
    public function doValidate($value)
    {
        if ((string) (int) $value != (string) $value) {
            return "err_not_int";
        }
        return parent::doValidate($value);
    }
}
