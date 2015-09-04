<?php
namespace Noce;

class Html_Cdata
{
    public $_cdata;
    
    public function __construct($cdata)
    {
        $this->_cdata = $cdata;
    }
    
    public function getEscapedHtml()
    {
        return $this->_cdata;
    }
    
    public function __toString()
    {
        return $this->getEscapedHtml();
    }
}
