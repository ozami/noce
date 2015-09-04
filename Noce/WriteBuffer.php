<?php
namespace Noce;

class WriteBuffer
{
    public $buffer = "";
    public $size = 0;
    public $writer;
    
    public function __construct($size, $writer)
    {
        $this->size = $size;
        $this->writer = $writer;
    }
    
    public function __destruct()
    {
        $this->flush();
    }
    
    public function write($data)
    {
        if (strlen($this->buffer) + strlen($data) > $this->size) {
            $this->flush();
        }
        $this->buffer .= $data;
    }
    
    public function flush()
    {
        call_user_func($this->writer, $this->buffer);
        $this->buffer = "";
    }
}
