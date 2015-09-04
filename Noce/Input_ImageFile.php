<?php
namespace Noce;

class Input_ImageFile extends Input_File
{
    public $inscribe = null;
    public $cover = null;
    public $saveFormat = Image::JPEG;
    public $saveOptions = array();
    
    public function getImage()
    {
        if ($this->isEmpty()) {
            return null;
        }
        return new Image($this->getPath());
    }
    
    public function filter($value)
    {
        if ($value == "") {
            return $value;
        }
        if ($this->inscribe || $this->cover) {
            $img = new Image($this->storage->getPath($value));
            if ($this->inscribe) {
                $img->inscribe($this->inscribe[0], $this->inscribe[1]);
            }
            if ($this->cover) {
                $img->cover($this->cover[0], $this->cover[1]);
            }
            $tmp = File::makeTempFile();
            $img->save($tmp, $this->saveFormat, $this->saveOptions);
            $this->storage->update($value, $tmp);
        }
        return $value;
    }
}
