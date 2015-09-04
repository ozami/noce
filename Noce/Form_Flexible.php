<?php
namespace Noce;

abstract class Form_Flexible extends Form
{
    public $_min;
    public $_max;
    public $_spare;
    public $_removeEmpty;

    abstract function createItem($i);

    public function __construct($args = array())
    {
        parent::__construct();
        $args += array(
            "min" => 0,
            "max" => PHP_INT_MAX, 
            "spare" => 0,
            "removeEmpty" => true);
        $this->setMin($args["min"]);
        $this->setMax($args["max"]);
        $this->setSpare($args["spare"]);
        $this->setRemoveEmpty($args["removeEmpty"]);
        $this->adjust();
    }

    public function setMin($min)
    {
        $this->_min = (int) $min;
    }

    public function setMax($max)
    {
        $this->_max = (int) $max;
    }

    public function setSpare($spare)
    {
        $this->_spare = (int) $spare;
    }

    public function setRemoveEmpty($removeEmpty)
    {
        $this->_removeEmpty = (bool) $removeEmpty;
    }

    public function getValue()
    {
        $values = array();
        foreach ($this->getItems() as $item) {
            if ($this->_removeEmpty && $item->isEmpty()) {
                continue;
            }
            $values[] = $item->getValue();
        }
        return $values;
    }

    public function setValue($values)
    {
        $values = array_slice($values, 0, $this->_max); // Re-indexed
        $items = array();
        foreach ($values as $i => $v) {
            $item = $this->createItem($i);
            $item->setValue($v);
            $items[$i] = $item;
        }
        $this->setItems($items); // internally calls adjust()
    }

    public function setItem($name, Input $item)
    {
        parent::setItem($name, $item);
        $this->adjust();
    }

    public function unsetItem($name)
    {
        parent::unsetItem($name);
        $this->adjust();
    }

    public function setItems(array $items)
    {
        parent::setItems($items);
        $this->adjust();
    }

    public function adjust()
    {
        $items = $this->getItems();
        // Remove empty
        if ($this->_removeEmpty) {
            foreach ($items as $i => $item) {
                if ($item->isEmpty()) {
                    unset($items[$i]);
                }
            }
        }
        // Re-index
        $items = array_values($items);
        // Reserve spare slots
        $size = max($this->_min, count($items) + $this->_spare);
        $size = min($this->_max, $size);
        for ($i = count($items); $i < $size; ++$i) {
            $items[] = $this->createItem($i);
        }
        parent::setItems($items);
    }
}
