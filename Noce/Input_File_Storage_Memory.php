<?php
namespace Noce;

class Input_File_Storage_Memory
{
    public $_items = array();
    
    public function add($path, $name)
    {
        $this->_items[] = array(
            "name" => $name,
            "data" => File::getContents($path)
        );
        $id = array_keys($this->_items);
        return $id[count($id) - 1];
    }
    
    public function update($id, $path)
    {
        $this->checkId($id);
        $this->_items[$id]["data"] = File::getContents($path);
    }
    
    public function getPath($id)
    {
        $type = $this->getFileType($id);
        $uri = "data:$type;base64,";
        $uri .= base64_encode($this->_items[$id]["data"]);
        return $uri;
    }
    
    public function getFileName($id)
    {
        $this->checkId($id);
        return $this->_items[$id]["name"];
    }
    
    public function getFileSize($id)
    {
        $this->checkId($id);
        return strlen($this->_items[$id]["data"]);
    }
    
    public function getFileType($id)
    {
        $this->checkId($id);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($this->_items[$id]["data"]);
    }
    
    public function checkId($id)
    {
        if (!isset($this->_items[$id])) {
            throw new \LogicException();
        }
    }
}
