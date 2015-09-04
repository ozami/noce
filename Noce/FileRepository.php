<?php
namespace Noce;

class FileRepository
{
    const ID_LENGTH = 8;
    public $_dir;

    public function __construct($dir)
    {
        $this->_dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function add($file, $name = null)
    {
        if ($name === null) {
            $name = basename($file);
        }
        $id = $this->_makeFile();
        $this->setName($id, $name);
        File::rename($file, $this->_locate($id));
        return $id;
    }
    
    public function addCopy($file, $name = null)
    {
        if ($name === null) {
            $name = basename($file);
        }
        $id = $this->makeFile();
        File::copy($file, $this->_locate($id));
        return $id;
    }
    
    public function update($id, $file)
    {
        File::rename($file, $this->_locate($id));
    }

    public function remove($id)
    {
        $path = $this->_locate($id);
        File::unlink($path);
        File::unlink($path . ".name");
    }
    
    public function move($id, $to)
    {
        $path = $this->_locate($id);
        File::rename($path, $to);
        File::unlink($path . ".name");
    }

    public function copy($id, $to)
    {
        File::copy($this->_locate($id), $to);
    }

    public function getName($id)
    {
        $path = $this->_locate($id) . ".name";
        return File::getContents($path);
    }

    public function setName($id, $name)
    {
        $path = $this->_locate($id) . ".name";
        File::putContents($path, $name);
    }
    
    public function getSize($id)
    {
        return File::filesize($this->_locate($id));
    }

    public function getType($id)
    {
        $finfo = new \finfo();
        return $finfo->file($this->_locate($id), FILEINFO_MIME_TYPE);
    }
    
    public function getPath($id)
    {
        return $this->_locate($id);
    }

    public function read($id)
    {
        return File::getContents($this->_locate($id));
    }

    public function write($id, $data)
    {
        File::putContents($this->_locate($id), $data);
    }

    public function output($id)
    {
        header("Content-Type: " . $this->getType($id));
        return File::readfile($this->_locate($id));
    }

    public function _locate($id, $check_exisitance = true)
    {
        if (strlen($id) != self::ID_LENGTH || preg_match("/[^0-9a-f]/", $id)) {
            throw new \RuntimeException();
        }
        $path = join(DIRECTORY_SEPARATOR, str_split($id, 2));
        $path = $this->_dir . $path;
        if ($check_exisitance && !is_file($path)) {
            throw new \RuntimeException();
        }
        return $path;
    }

    public function _makeFile()
    {
        $count = 5;
        while ($count) {
            $id = $this->_makeId();
            $path = $this->_locate($id, false);
            $dir = dirname($path);
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException();
            }
            $fp = @fopen($path, "x");
            if ($fp) {
                fclose($fp);
                return $id;
            }
            if (!file_exists($path)) {
                --$count;
            }
        }
        throw new \RuntimeException();
    }

    public function _makeId()
    {
        $id = bin2hex(Crypt::random(self::ID_LENGTH));
        $id = substr($id, 0, self::ID_LENGTH);
        return $id;
    }
}
