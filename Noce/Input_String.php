<?php
namespace Noce;

class Input_String extends Input
{
    const CASE_UPPER = MB_CASE_UPPER;
    const CASE_LOWER = MB_CASE_LOWER;
    const CASE_TITLE = MB_CASE_TITLE;
    const TRIM_LEFT = "left";
    const TRIM_RIGHT = "right";
    const TRIM_BOTH = "both";

    public $mb = "";
    public $case = "";
    public $multiline = false;
    public $noSpace = false;
    public $noControl = true;
    public $eol = "\n"; // used only when $multiline is true. set false for no conversion
    public $trim = self::TRIM_BOTH;
    public $utf8Only = true;
    public $minLength = 0;
    public $maxLength = PHP_INT_MAX;
    public $regex = null;
    
    public function filter($value)
    {
        $value = (string) $value;
        if ($this->mb != "") {
            $value = mb_convert_kana($value, $this->mb, "UTF-8");
        }
        if ($this->case != "") {
            $value = mb_convert_case($value, $this->case, "UTF-8");
        }
        if ($this->multiline) {
            if ($this->eol !== false) {
                $value = preg_replace("/(\r\n|\r|\n)/u", $this->eol, $value);
            }
        }
        else {
            $value = preg_replace("/[\r\n]/u", " ", $value);
        }
        if ($this->noControl) {
            // 0x00-0x1f, 0x7f and 0x00a0 (no-break space, which is 0xc2a0 in UTF-8) except CR and LF
            $value = preg_replace("/[\\x00-\\x09\\x0B\\x0c\\x0e-\\x1f\\x7f\\x{00a0}]/u", " ", $value);
        }
        if ($this->noSpace) {
            $value = preg_replace("/[[:space:]\\x{00a0}　]/u", "", $value);
        }
        if ($this->trim == self::TRIM_LEFT || $this->trim == self::TRIM_BOTH) {
            $value = preg_replace("/^[[:space:]\\x{00a0}　]+/u", "", $value);
        }
        if ($this->trim == self::TRIM_RIGHT || $this->trim == self::TRIM_BOTH) {
            $value = preg_replace("/[[:space:]\\x{00a0}　]+$/u", "", $value);
        }
        return $value;
    }
    
    public function doValidate($value)
    {
        if ($this->utf8Only && !preg_match("//u", $value)) {
            return "err_not_utf8";
        }
        $length = mb_strlen($value, "UTF-8");
        if ($length < $this->minLength) {
            return "err_too_short";
        }
        if ($length > $this->maxLength) {
            return "err_too_long";
        }
        if (isset($this->regex) && !preg_match($this->regex, $value)) {
            return "err_regex_unmatch";
        }
    }
}
