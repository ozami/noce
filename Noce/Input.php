<?php
namespace Noce;

class Input
{
    public $_value = "";
    public $_label = "";
    public $_required = true;
    public $_readOnly = false;
    public $_disabled = false;
    public $_preFilter = null;
    public $_postFilter = null;
    public $_validators = array();
    public $_err_table = array();
    public $_error = null;
    public $_valid = false;
    public $_observers = array();

    public function __construct($args = array())
    {
        $this->set($args);
    }
    
    public function set(array $args)
    {
        foreach ($args as $name => $value) {
            $setter = "set" . ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
            else {
                $this->$name = $value;
            }
        }
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        if ($this->_readOnly) {
            return;
        }
        if (is_callable($this->_preFilter)) {
            $value = call_user_func($this->_preFilter, $value);
        }
        $value = $this->filter($value);
        if (is_callable($this->_postFilter)) {
            $value = call_user_func($this->_postFilter, $value);
        }
        $this->_value = $value;
        $this->_valid = false;
        $this->_error = null;
        
        foreach ($this->_observers as $observer) {
            call_user_func($observer, $value);
        }
    }

    public function clear()
    {
        $this->setValue("");
    }
    
    public function getLabel()
    {
        return $this->_label;
    }
    
    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function getRequired()
    {
        return $this->_required;
    }

    public function setRequired($required)
    {
        $this->_required = $required;
    }

    public function getReadOnly()
    {
        return $this->_readOnly;
    }

    public function setReadOnly($readOnly)
    {
        $this->_readOnly = $readOnly;
    }
    
    public function isEmpty()
    {
        return strlen($this->_value) == 0;
    }
    
    public function getDisabled()
    {
        return $this->_disabled;
    }
    
    public function setDisabled($disabled)
    {
        $this->_disabled = (bool) $disabled;
        if ($this->_disabled) {
            $this->_valid = false;
            $this->_error = null;
        }
    }
    
    public function getError()
    {
        return $this->_error;
    }

    public function setError($error)
    {
        if ($this->_disabled) {
            return;
        }
        if (isset($this->_err_table[$error])) {
            $error = $this->_err_table[$error];
        }
        $this->_error = $error;
    }

    public function getErrorMessage()
    {
        if (!$this->_error) {
            return null;
        }
        return new Message($this->_error, array("input" => $this));
    }

    public function getErrorString()
    {
        $msg = $this->getErrorMessage();
        if ($msg === null) {
            return null;
        }
        return $msg->string();
    }

    public function isError()
    {
        return (bool) $this->getError();
    }

    public function getValid()
    {
        return $this->_valid;
    }

    public function setValid($valid)
    {
        if ($this->_disabled) {
            return;
        }
        $this->_valid = (bool) $valid;
    }

    public function isValid()
    {
        return $this->getValid();
    }

    public function filter($value)
    {
        return $value;
    }
    
    public function getPreFilter()
    {
        return $this->_preFilter;
    }
    
    public function setPreFilter($filter)
    {
        $this->_preFilter = $filter;
    }
    
    public function getPostFilter()
    {
        return $this->_postFilter;
    }
    
    public function setPostFilter($filter)
    {
        $this->_postFilter = $filter;
    }
    
    public function validate($value = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }
        if ($this->_disabled) {
            return false;
        }
        $this->_valid = false;
        $this->_error = null;
        // Check if the value is empty
        if ($this->isEmpty()) {
            // Error if this is required
            if ($this->_required) {
                $this->setError("err_empty");
                return false;
            }
            // No more validation required
            $this->_valid = true;
            return true;
        }
        $this->setError($this->doValidate($this->getValue()));
        if ($this->_error !== null) {
            return false;
        }
        // Call validators
        if (isset($this->_validators)) {
            foreach ($this->_validators as $validator) {
                $this->setError(call_user_func($validator, $this->getValue(), $this));
                if ($this->_error !== null) {
                    return false;
                }
            }
        }
        $this->_valid = true;
        return true;
    }
    
    public function doValidate($value)
    {
        return null;
    }
    
    public function getValidators()
    {
        return $this->_validators;
    }
    
    public function setValidators($validators)
    {
        $this->_validators = $validators;
    }
    
    public function setValidator($validator)
    {
        $this->setValidators(array($validator));
    }
    
    public function getErrorTable()
    {
        return $this->_err_table;
    }
    
    public function setErrorTable(array $table)
    {
        $this->_err_table = $table;
    }
    
    public function setObservers(array $observers)
    {
        $this->_observers = $observers;
    }
    
    public function addObserver($observer)
    {
        $this->_observers[] = $observer;
    }
    
    public function setObserver($observer)
    {
        $this->setObservers(array($observer));
    }
    
    public function dump()
    {
        return array(
            "value" => $this->getValue(),
            "error" => $this->getError(),
            "valid" => $this->getValid()
        );
    }
    
    public function restore($data)
    {
        if (isset($data["value"])) {
            // don't use setValue() so that we can 
            // restore the value of readOnly input
            $this->_value = $data["value"];
        }
        if (isset($data["error"])) {
            $this->_error = $data["error"];
        }
        if (isset($data["valid"])) {
            $this->_valid = $data["valid"];
        }
    }
    
    public function __toString()
    {
        return $this->_value;
    }
    
    public function __get($name)
    {
        $getter = "get" . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        throw new \Exception("Undefined property '$name' via __get()");
    }
    
    public function __set($name, $value)
    {
        $setter = "set" . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
        else {
            $this->$name = $value;
        }
    }
    
    public function __isset($name)
    {
        $getter = "get" . ucfirst($name);
        if (!method_exists($this, $getter)) {
            return false;
        }
        return $this->$getter() !== null;
    }
}
