<?php
namespace Noce;

class File
{
    public static $_autoDeleteFiles = array();
    
    public static function __callStatic($name, $args)
    {
        $renamed = array(
            "getContents" => "file_get_contents",
            "putContents" => "file_put_contents");
        if (isset($renamed[$name])) {
            $name = $renamed[$name];
        }
        $false_on_error_funcs = array(
            "copy", "fgetc", "fgets", "file", "file_get_contents", "fopen", 
            "fputs", "fwrite", "fread", "fscanf", "ftell", "ftruncate", "glob", "mkdir",
            "file_put_contents", "filesize", "link", "readfile", "rename", "rmdir",
            "scandir", "stat", "tmpfile", "tempnam", "touch", "unlink");
        if (in_array($name, $false_on_error_funcs)) {
            $r = @call_user_func_array($name, $args);
            if ($r === false) {
                $e = error_get_last();
                throw new \RuntimeException($e["message"]);
            }
            return $r;
        }
        throw new \BadMethodCallException();
    }

    public static function fseek($handle, $offset, $whence = SEEK_SET)
    {
        if (@fseek($handle, $offset, $whence) != 0) {
            $e = error_get_last();
            throw new \RuntimeException($e["message"]);
        }
    }

    public static function uniqueName(array $options = array())
    {
        $options += array(
            "dir" => sys_get_temp_dir(),
            "prefix" => "",
            "suffix" => ""
        );
        $options["dir"] = rtrim($options["dir"], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        // create without using tmpname(), because tmpname() on Windows
        // cannot handle prefix longer than 3 characters.
        $count = 5;
        while ($count) {
            // TODO: Use Crypt::random()
            $path = $options["dir"]
                . $options["prefix"]
                . sha1(
                    mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand()
                )
                . $options["suffix"];
            $fp = @fopen($path, "x");
            if ($fp) {
                fclose($fp);
                return $path;
            }
            if (!file_exists($path)) {
                --$count;
            }
        }
        throw new \RuntimeException("err_create_unique_name");
    }
    
    public static function makeTempFile(array $options = array())
    {
        $path = self::uniqueName($options);
        self::registerAutoDeleteFile($path);
        return $path;
    }
    
    public static function registerAutoDeleteFile($file)
    {
        if (!self::$_autoDeleteFiles) {
            register_shutdown_function(array(__CLASS__, "doAutoDeleteFile"));
        }
        self::$_autoDeleteFiles[] = $file;
    }
    
    public static function unregisterAutoDeleteFile($file)
    {
        self::$_autoDeleteFiles = array_diff(self::$_autoDeleteFiles, array($file));
    }
    
    public static function doAutoDeleteFile()
    {
        foreach (array_unique(self::$_autoDeleteFiles) as $file) {
            @unlink($file);
        }
        self::$_autoDeleteFiles = array();
    }
}
