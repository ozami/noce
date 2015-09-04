<?php
namespace Noce;

class Validator_Reentry
{
    function __construct($original)
    {
        $this->_original = $original;
    }

    function __invoke($value)
    {
        if ($value != $this->_original->getValue()) {
            return "err_reentry_not_matched";
        }
    }
}
