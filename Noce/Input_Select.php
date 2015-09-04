<?php
namespace Noce;

class Input_Select extends Input
{
    public $_options = array();
    public $_multiple = false;
    public $minCount = 0;
    public $maxCount = PHP_INT_MAX;
    
    public function __construct($args = array())
    {
        if (@$args["multiple"]) {
            $this->_value = array();
        }
        parent::__construct($args);
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    public function getMultiple()
    {
        return $this->_multiple;
    }

    public function setMultiple($multiple)
    {
        $this->_multiple = $multiple;
    }

    public function isEmpty()
    {
        if ($this->_multiple) {
            return !$this->getValue();
        }
        return parent::isEmpty();
    }

    public function setValue($value)
    {
        if ($this->_multiple) {
            $value = (array) $value;
        }
        parent::setValue($value);
    }

    public function clear()
    {
        if ($this->_multiple) {
            $this->setValue(array());
        }
        else {
            $this->setValue("");
        }
    }

    public function getOptionsLabel($value)
    {
        return @$this->_options[$value];
    }

    public function getSelected()
    {
        if ($this->_multiple) {
            return ArrayX::pick($this->_options, $this->getValue());
        }
        return @$this->_options[$this->getValue()];
    }
    
    public function doValidate($value)
    {
        $options = $this->getOptions();
        foreach ((array) $value as $v) {
            if (!isset($options[$v])) {
                return "err_not_in_options";
            }
        }
        if ($this->_multiple) {
            $count = count($value);
            if ($count < $this->minCount) {
                return "err_too_few";
            }
            if ($count > $this->maxCount) {
                return "err_too_many";
            }
        }
    }
    
    public function __toString()
    {
        return $this->getSelected();
    }
}
