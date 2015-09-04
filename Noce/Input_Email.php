<?php
namespace Noce;

class Input_Email extends Input_String
{
    public $mb = "as";
    public $noSpace = true;
    
    public function doValidate($value)
    {
        $atext = "[-!#$%&'*+/=?^_`{|}~A-Za-z0-9]";
        $atom  = "$atext+";
        $dot_atom = "$atom(\\.$atom)*";
        if (!preg_match(":^$dot_atom@$dot_atom$:", $value)) {
            return "err_invalid_email";
        }
        return parent::doValidate($value);
    }
}
