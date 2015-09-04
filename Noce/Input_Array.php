<?php
namespace Noce;

class Input_Array extends Input
{
    public $minCount = 0;
    public $maxCount = PHP_INT_MAX;
    
    public function __construct($args = array())
    {
        if (!isset($args["value"])) {
            $args["value"] = array();
        }
        parent::__construct($args);
    }
    
    public function isEmpty()
    {
        return !$this->getValue();
    }

    public function setValue($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        parent::setValue($value);
    }

    public function clear()
    {
        $this->setValue(array());
    }

    public function getOptionsLabel($value)
    {
        return @$this->_options[$value];
    }

    public function doValidate($value)
    {
        $count = count($value);
        if ($count < $this->minCount) {
            return "err_too_few";
        }
        if ($count > $this->maxCount) {
            return "err_too_many";
        }
    }
}
