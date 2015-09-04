<?php
namespace Noce;

class Log
{
    public $_rotation = false;
    
    public function __construct($file, $rotation = false)
    {
        $this->_file = $file;
        $this->setRotation($rotation);
    }
    
    public function setRotation($rotation)
    {
        if ($rotation === false) {
            $this->_rotation = false;
            return;
        }
        if (!is_array($rotation)) {
            throw new \Exception();
        }
        $rotation += array(
            "size" => 10 * 1024 * 1024,
            "history" => 8,
            "probability" => 0.01
        );
        $rotation["probability"] = (int) (1 / $rotation["probability"]) - 1;
        $this->_rotation = $rotation;
    }

    public function write($log)
    {
        if ($this->_rotation && mt_rand(0, $this->_rotation["probability"]) == 0) {
            $this->rotate();
        }
        file_put_contents($this->_file, date("[Y-m-d H:i:s]") . " $log\n", FILE_APPEND | LOCK_EX);
    }

    public function rotate()
    {
        $stat = @stat($this->_file);
        if (!$stat || $stat["size"] < $this->_rotation["size"]) {
            return;
        }
        $no_abort = new NoAbort();
        if (!rename($this->_file, $this->_file . ".0")) {
            throw new \Exception();
        }
        $logs = glob($this->_file . ".*");
        $numbers = array_map(function($log) {
            if (preg_match("#\\.([0-9]+)$#", $log, $match)) {
                return (int) $match[1];
            }
        }, $logs);
        rsort($numbers);
        foreach ($numbers as $i) {
            $current = $this->_file . ".$i";
            if ($i >= $this->_rotation["history"]) {
                if (!unlink($current)) {
                    throw new \Exception();
                }
            }
            else {
                $new = $this->_file . "." . ($i + 1);
                if (!rename($current, $new)) {
                    throw new \Exception();
                }
            }
        }
    }

    public function truncate()
    {
        fclose(fopen($this->_file, "w"));
    }
}
