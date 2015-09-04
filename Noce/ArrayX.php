<?php
namespace Noce;

class ArrayX
{
    public static function pathGet(array $a, $path, $separator = "/")
    {
        if (!is_array($path)) {
            $path = explode($separator, $path);
            if ($path[0] == "") {
                array_shift($path);
            }
        }
        foreach ($path as $node) {
            $a = @$a[$node];
        }
        return $a;
    }
    
    public static function pathSet(&$a, $path, $value)
    {
        $i = array_shift($path);
        if (!$path) {
            // Add an item if the key is emtpy
            if ($i == "") {
                $a[] = $value;
            }
            else {
                $a[$i] = $value;
            }
            return;
        }
        self::pathSet($a[$i], $path, $value);
    }
    
    /**
    * 配列から指定したキーの値を取り出す
    *
    * @param $a array 配列
    * @param $keys array 取り出すキーの配列。
    * @return array
    */
    public static function pick(array $a, array $keys)
    {
        return array_intersect_key($a, array_flip($keys));
    }
    
    /**
    * 配列から指定したキー以外の値を取り出す
    */
    public static function exclude(array $a, array $keys)
    {
        return array_diff_key($a, array_flip($keys));
    }
    
    public static function removeValue(array &$array, $value)
    {
        $count = 0;
        while (true) {
            $i = array_search($value, $array);
            if ($i === false) {
                break;
            }
            unset($array[$i]);
            ++$count;
        }
        return $count;
    }
    
    public static function flatten($array)
    {
        $flat = array();
        if (is_array($array)) {
            foreach ($array as $i) {
                $flat = array_merge($flat, self::flatten($i));
            } 
        }
        else {
            $flat[] = $array;
        }
        return $flat;
    }
    
    public static function twin(array $a)
    {
        return array_combine($a, $a);
    }
    
    public static function uSearch($needle, array $haystack, $isEqual)
    {
        foreach ($haystack as $i => $value) {
            if (call_user_func($isEqual, $value, $needle)) {
                return $i;
            }
        }
        return false;
    }
    
    public static function uUnique(array $array, $isEqual)
    {
        $unique = array();
        foreach ($array as $i => $value) {
            if (self::uSearch($value, $unique, $isEqual) === false) {
                $unique[$i] = $value;
            }
        }
        return $unique;
    }
    
    /**
     * group items
     *
     * @param $array array array to be grouped
     * @param $callback callable|string|array
     * @return array grouped array
     */
    public static function group(array $array, $callback)
    {
        // wrap string/array key with function
        if (!is_callable($callback)) {
            $keys = (array) $callback;
            $callback = function ($item) use ($keys) {
                return array_map(function($key) use ($item) {
                    return @$item[$key];
                }, $keys);
            };
        }
        // group
        $grouped = array();
        foreach ($array as $i => $v) {
            $path = (array) call_user_func($callback, $v, $i);
            $path[] = ""; // so that the value will be appended
            self::pathSet($grouped, $path, $v);
        }
        return $grouped;
    }
}
