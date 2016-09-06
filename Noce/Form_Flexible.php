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
    }

    public function setMin($min)
    {
        $this->_min = (int) $min;
        $this->adjust();
    }

    public function setMax($max)
    {
        $this->_max = (int) $max;
        $this->adjust();
    }

    public function setSpare($spare)
    {
        $this->_spare = (int) $spare;
        $this->adjust();
    }

    public function setRemoveEmpty($removeEmpty)
    {
        $this->_removeEmpty = (bool) $removeEmpty;
        $this->adjust();
    }

    public function getValue()
    {
        $this->adjust(); // needed for removing empty values
        return parent::getValue();
    }

    public function setValue($values)
    {
        $this->adjust(count($values));
        parent::setValue($values);
        $this->adjust();
    }

    public function setItem($name, $item)
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

    public function adjust($min_size = 0)
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
        $size = max($this->_min, $min_size, count($items) + $this->_spare);
        $size = min($this->_max, $size);
        for ($i = count($items); $i < $size; ++$i) {
            $items[] = $this->createItem($i);
        }
        parent::setItems($items);
    }
}
