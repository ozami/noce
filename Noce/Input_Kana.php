<?php
namespace Noce;

class Input_Kana extends Input_String
{
    public $mb = "CKV";

    public function doValidate($value)
    {
        if (preg_match("/[^ァ-ヴー]/u", $value)) {
            return "err_not_kana";
        }
        return parent::doValidate($value);
    }
}
