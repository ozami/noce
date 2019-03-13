<?php
namespace Noce;

class Input_File extends Input
{
    public $storage;
    public $minSize = 0;
    public $maxSize = INF;
    public $types = array();
    public $_uploadError;
    
    public function __construct($args = array())
    {
        parent::__construct($args);
    }
    
    public function getPath()
    {
        if (!$this->storage) {
            throw new \LogicException();
        }
        return $this->storage->getPath($this->getValue());
    }
    
    public function getFileName()
    {
        if (!$this->storage) {
            throw new \LogicException();
        }
        return $this->storage->getFileName($this->getValue());
    }
    
    public function getFileType()
    {
        if (!$this->storage) {
            throw new \LogicException();
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($this->getPath());
    }
    
    public function setValue($value)
    {
        $this->_uploadError = null;
        
        if ($value == "") {
            parent::setValue("");
            return;
        }
        if (!($value instanceof UploadedFile)) {
            parent::setValue("");
            throw new RuntimeException("err_not_an_uploaded");
        }
        $this->_uploadError = $value->error;
        if ($this->_uploadError != UPLOAD_ERR_OK) {
            parent::setValue("");
            return;
        }
        
        // store file
        if (!$this->storage) {
            throw new \LogicException();
        }
        $value = $this->storage->add($value->path, $value->name);
        parent::setValue($value);
    }

    public function doValidate($value)
    {
        switch ($this->_uploadError) {
            case UPLOAD_ERR_OK:
            break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
            return "err_file_too_large";

            case UPLOAD_ERR_PARTIAL:
            return "err_partial_file"; // TODO

            case UPLOAD_ERR_NO_FILE:
            return "err_empty";

            default:
            return "err_sys"; // FIXME
        }
        if (!$this->storage) {
            throw new \LogicException();
        }
        $size = $this->storage->getFileSize($value);
        if ($size > $this->maxSize) {
            return "err_file_too_large";
        }
        if ($size < $this->minSize) {
            return "err_file_too_small";
        }
        if ($this->types) {
            $type = $this->storage->getFileType($value);
            if (!in_array($type, $this->types)) {
                return "err_file_type";
            }
        }
    }
}
