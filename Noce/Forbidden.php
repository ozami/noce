<?php
namespace Noce;

class Forbidden extends \RuntimeException
{
    public $_data;
    
    public function __construct($data = null)
    {
        parent::__construct();
        $this->_data = $data;
    }
    
    public function getData()
    {
        return $this->_data;
    }
}
