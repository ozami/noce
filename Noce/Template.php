<?php
namespace Noce;

class Template
{
    public $_src;
    public $_filters = array(
        array(__CLASS__, "filterPrintf"),
        array(__CLASS__, "filterEcho")
    );
    
    public function __construct($src)
    {
        if ($src instanceof \SplFileInfo) {
            $src = @file_get_contents($src->getPathname());
            if ($src === false) {
                throw new \Exception("Template file not found.");
            }
        }
        $this->_src = $src;
    }

    public function render($args)
    {
        extract($args);
        if (eval("?>" . $this->filter($this->_src)) === false) {
            throw new \Exception("Syntax Error in template.");
        }
    }
    
    public function string($args)
    {
        try {
            ob_start();
            $this->render($args);
            return ob_get_clean();
        }
        catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
    
    public function filter($src)
    {
        foreach ($this->_filters as $filter) {
            $src = call_user_func($filter, $src);
        }
        return $src;
    }
    
    public function addFilter($filter)
    {
        $this->_filters[] = $filter;
    }
    
    public static function filterPrintf($src)
    {
        return preg_replace("/<[?]%(.*?)[?]>/us", "<?= sprintf($1) ?>", $src);
    }
    
    public static function filterEcho($src)
    {
        return preg_replace("/(<[?]=.*?[?]>)(\r\n|\r|\n)?/us", "$1$2$2", $src);
    }
}
