<?php
namespace Noce;

class Validator_Alphanumeric
{
    function __invoke($value)
    {
        if (preg_match("/[^0-9a-zA-Z]/u", $value)) {
            return "err_not_alphanumeric";
        }
    }
}
