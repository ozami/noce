<?php
namespace Noce;

class Input_File_Storage_FileRepository
{
    public $_repo;
    
    public function __construct(FileRepository $repo)
    {
        $this->_repo = $repo;
    }
    
    public function add($path, $name)
    {
        return $this->_repo->add($path, $name);
    }
    
    public function update($id, $path)
    {
        $this->_repo->update($id, $path);
    }
    
    public function getPath($id)
    {
        return $this->_repo->getPath($id);
    }
    
    public function getFileName($id)
    {
        return $this->_repo->getName($id);
    }
    
    public function getFileSize($id)
    {
        return $this->_repo->getSize($id);
    }
    
    public function getFileType($id)
    {
        return $this->_repo->getType($id);
    }
}
