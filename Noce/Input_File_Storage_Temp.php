<?php
namespace Noce;

class Input_File_Storage_Temp
{
    public $_items = array();
    
    public function add($path, $name)
    {
        $_items[$path] = $name;
        return $path;
    }
    
    public function update($id, $path)
    {
        $this->checkId($id);
        File::rename($path, $id);
    }
    
    public function getPath($id)
    {
        $this->checkId($id);
        return $id;
    }
    
    public function getFileName($id)
    {
        $this->checkId($id);
        return $this->_items[$id];
    }
    
    public function getFileSize($id)
    {
        $this->checkId($id);
        return filesize($id);
    }
    
    public function getFileType($id)
    {
        $this->checkId($id);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($id);
    }
    
    public function checkId($id)
    {
        if (!isset($this->_items[$id])) {
            throw new \LogicException();
        }
    }
}
