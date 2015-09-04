<?php
namespace Noce;

class Input_Url extends Input_String
{
    public $mb = "as";
    public $noSpace = true;
    public $schemes = array("http", "https"); // lower case only
    
    public function doValidate($value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return "err_invalid_url";
        }
        if ($this->schemes && !in_array(strtolower(parse_url($value, PHP_URL_SCHEME)), $this->schemes)) {
            return "err_invalid_url_scheme";
        }
        return parent::doValidate($value);
    }
}
