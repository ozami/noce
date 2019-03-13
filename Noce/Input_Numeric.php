<?php
namespace Noce;

define("NOCE_INPUT_NUMERIC_MINUS_INF", -INF);

class Input_Numeric extends Input
{
    public $min = NOCE_INPUT_NUMERIC_MINUS_INF;
    public $max = INF;
    
    public function filter($value)
    {
        $value = mb_convert_kana($value, "as");
        $value = preg_replace("/[[:space:]]/u", "", $value);
        return $value;
    }
    
    public function doValidate($value)
    {
        if ($value < $this->min) {
            return "err_too_small";
        }
        if ($value > $this->max) {
            return "err_too_large";
        }
    }

    public function getFormatted($decimals = 0)
    {
        // TODO: support locale
        return number_format($this->getValue(), $decimals);
    }
}
