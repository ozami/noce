<?php
namespace Noce;

class Form implements \ArrayAccess, \Iterator, \Countable, \Serializable
{
    public $_items = array();
    public $_current = 0;
    public $_disabled = false;
    
    public function __construct($value = array())
    {
        $this->init();
        $this->setValue($value);
    }
    
    /**
     * Create items and add them to the form
     *
     * called by __construct() and unserialize()
     */
    public function init()
    {
    }
    
    public function getValue($items = array())
    {
        if ($this->_disabled) {
            return array();
        }
        if ($items) {
            $values = array();
            foreach ($items as $i) {
                $item = $this->getItem($i);
                if ($item && !$item->getDisabled()) {
                    $values[$i] = $item->getValue();
                }
            }
            return $values;
        }

        $values = array();
        foreach ($this->getEnabledItems() as $i => $item) {
            $values[$i] = $item->getValue();
        }
        return $values;
    }

    public function setValue($values)
    {
        foreach ($this->_items as $i => $item) {
            if (isset($values[$i])) {
                $item->setValue($values[$i]);
            }
        }
    }

    public function clear()
    {
        foreach ($this->_items as $i => $item) {
            $item->clear();
        }
    }

    public function isEmpty()
    {
        if ($this->_disabled) {
            return true;
        }
        foreach ($this->getEnabledItems() as $i => $item) {
            if (!$item->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    public function getFilled()
    {
        if ($this->_disabled) {
            return array();
        }
        $values = array();
        foreach ($this->getEnabledItems() as $i => $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($item instanceof Form) {
                $values[$i] = $item->getFilled();
            }
            else {
                $values[$i] = $item->getValue();
            }
        }
        return $values;
    }
    
    public function setRequired($required)
    {
        foreach ($this->_items as $i => $item) {
            $item->setRequired($required);
        }
    }
    
    public function getDisabled()
    {
        return $this->_disabled;
    }
    
    public function setDisabled($disabled)
    {
        $this->_disabled = (bool) $disabled;
    }
    
    public function isValid()
    {
        if ($this->_disabled) {
            return false;
        }
        foreach ($this->getEnabledItems() as $i => $item) {
            if (!$item->isValid()) {
                return false;
            }
        }
        return true;
    }

    public function validate($value = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }
        if ($this->_disabled) {
            return false;
        }
        foreach ($this->getEnabledItems() as $item) {
            $item->validate();
        }
        return $this->isValid();
    }

    public function isError()
    {
        if ($this->_disabled) {
            return false;
        }
        foreach ($this->getEnabledItems() as $item) {
            if ($item->isError()) {
                return true;
            }
        }
        return false;
    }

    public function setError($errors)
    {
        if ($this->_disabled) {
            return;
        }
        foreach ($this->_items as $i => $item) {
            $item->setError(@$errors[$i]);
        }
    }

    public function getError()
    {
        if ($this->_disabled) {
            return array();
        }
        $errs = array();
        foreach ($this->getEnabledItems() as $i => $item) {
            $errs[$i] = $item->getError();
        }
        return $errs;
    }

    public function getErrorMessage()
    {
        if ($this->_disabled) {
            return array();
        }
        $msgs = array();
        foreach ($this->getEnabledItems() as $i => $item) {
            $msgs[$i] = $item->getErrorMessage();
        }
        return $msgs;
    }

    public function getErrorString()
    {
        if ($this->_disabled) {
            return array();
        }
        $msgs = array();
        foreach ($this->getEnabledItems() as $i => $item) {
            $msgs[$i] = $item->getErrorString();
        }
        return $msgs;
    }

    public function getFlatErrors()
    {
        if ($this->_disabled) {
            return array();
        }
        $errs = array();
        foreach ($this->getEnabledItems() as $item) {
            if ($item instanceof Form) {
                $errs = array_merge($errs, $item->getFlatErrors());
            }
            else {
                if ($item->isError()) {
                    $errs = array_merge($errs, array($item->getError()));
                }
            }
        }
        return $errs;
    }

    public function getItem($itemPath)
    {
        return ArrayX::pathGet($this->_items, $itemPath);
    }
    
    public function setItem($name, $item)
    {
        if ($name === null) {
            $this->_items[] = $item;
        }
        else {
            $this->_items[$name] = $item;
        }
    }

    public function unsetItem($name)
    {
        unset($this->_items[$name]);
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function getEnabledItems()
    {
        if ($this->_disabled) {
            return array();
        }
        return array_filter($this->_items, function($item) {
            return !$item->getDisabled();
        });
    }

    public function setItems(array $items)
    {
        $this->_items = $items;
    }
    
    public function dump()
    {
        $data = array();
        foreach ($this->_items as $name => $item) {
            $data[$name] = $item->dump();
        }
        return $data;
    }
    
    public function restore($data)
    {
        foreach ($this->_items as $name => $item) {
            if (isset($data[$name])) {
                $item->restore($data[$name]);
            }
        }
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
    
    
    // 
    // ArrayAccess interface
    //

    public function offsetExists($offset)
    {
        return isset($this->_items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getItem($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setItem($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->unsetItem($offset);
    }

    //
    // Iterator interface
    //

    public function current()
    {
        $key = @$this->key();
        if ($key === null) {
            return false;
        }
        return $this->_items[$key];
    }

    public function key()
    {
        $key = array_keys($this->_items);
        return @$key[$this->_current];
    }

    public function next()
    {
        ++$this->_current;
    }

    public function rewind()
    {
        $this->_current = 0;
    }

    public function valid()
    {
        return @$this->key() !== null;
    }

    //
    // Countable interface
    //

    public function count()
    {
        return count($this->_items);
    }

    //
    // Serializable interface
    //

    public function serialize()
    {
        return serialize($this->dump());
    }

    public function unserialize($serialized)
    {
        $this->init();
        $this->restore(unserialize($serialized));
    }
}
