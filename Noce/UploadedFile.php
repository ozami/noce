<?php
namespace Noce;

class UploadedFile
{
    public $name;
    public $type;
    public $size;
    public $error;
    public $path;

    public function __construct(array $uploaded)
    {
        $this->name = $uploaded["name"];
        $this->type = $uploaded["type"];
        $this->size = $uploaded["size"];
        $this->error = $uploaded["error"];
        $this->path = @$uploaded["tmp_name"];
    }
    
    public static function mergePostAndFiles($class = __CLASS__)
    {
        $merge = function ($files, &$post) use (&$merge) {
            foreach ($files as $key => $value) {
                if (is_array($value)) {
                    $post[$key] = array();
                    $merge($value, $post[$key]);
                }
                else {
                    $post[$key] = $value;
                }
            }
        };
        $files = self::instantiateFiles($class);
        $post = $_POST;
        $merge($files, $post);
        return $post;
    }

    public static function instantiateFiles($class = __CLASS__)
    {
        $files = self::fixFilesTree();

        $conv = function ($files) use (&$conv, $class) {
            if (!$files) {
                return $files;
            }
            if (!is_array(current($files))) {
                return new $class($files);
            }
            foreach ($files as $key => $value) {
                $files[$key] = $conv($files[$key]);
            }
            return $files;
        };
        $files = $conv($files);
        return $files;
    }

    public static function fixFilesTree()
    {
        // TODO: avoid conflict when both $_FILES and $_POST have same key
        $files = array();
        $fix = function (&$files, $values, $prop) use (&$fix) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $fix($files[$key], $value, $prop);
                } else {
                    $files[$key][$prop] = $value;
                }
            }
        };
        foreach ($_FILES as $name => $props) {
            foreach ($props as $prop => $value) {
                if (is_array($value)) {
                    $fix($files[$name], $value, $prop);
                } else {
                    $files[$name][$prop] = $value;
                }
            }
        }
        return $files;
    }
}
