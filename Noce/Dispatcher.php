<?php
namespace Noce;

class Dispatcher
{
    public $_root_path = "";
    
    public function __construct($root_path = "/")
    {
        $this->setRootPath($root_path);
    }
    
    public function run()
    {
        list ($controller, $action) = $this->route();
        if (!class_exists($controller, true)) {
            return array();
        }
        $controller = $this->createController($controller);
        return $controller->act($action);
    }
    
    public function route()
    {
        $path = $this->toRelative($_SERVER["SCRIPT_NAME"]);
        $class = dirname($path);
        if ($class == ".") {
            $class = "Root";
        }
        else {
            $class = str_replace(array("_", "-"), " ", $class);
            $class = ucwords($class);
            $class = str_replace(" ", "", $class);
            $class = str_replace("/", " ", $class);
            $class = ucwords($class);
            $class = str_replace(" ", "_", $class);
        }
        $controller = "Controller_" . $class;
        if (!class_exists($controller, true)) {
            return;
        }
        $action = basename($path);
        return array($controller, $action);
    }
    
    public function toRelative($path)
    {
        $path = ltrim($path, "/");
        if ($this->_root_path != "") {
            if (strpos($path, $this->_root_path) !== 0) {
                throw new \LogicException();
            }
            $path = substr($path, strlen($this->_root_path));
        }
        $path = ltrim($path, "/");
        return $path;
    }
    
    public function setRootPath($root_path)
    {
        $this->_root_path = trim($root_path, "/");
    }
    
    public function createController($class)
    {
        return new $class();
    }
}
