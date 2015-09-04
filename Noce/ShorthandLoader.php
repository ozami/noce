<?php
namespace Noce;

class ShorthandLoader
{
    public static function load($names = array())
    {
        $shorthands = array(
            "Debug" => array("d", "dd"),
            "Html" => array("p"));
        $names = (array) $names;
        $parsed = array();
        foreach ($names as $name) {
            @list ($class, $func) = explode("::", $name, 2);
            if (!isset($shorthands[$class])) {
                throw new \Exception();
            }
            if ($func == "") {
                $parsed[$class] = $shorthands[$class];
            }
            else {
                if (!in_array($func, $shorthands[$class])) {
                    throw new \Exception();
                }
                $parsed[$class][] = $func;
            }
        }
        if (!$parsed) {
            $parsed = $shorthands;
        }
        foreach ($parsed as $class => $funcs) {
            foreach ($funcs as $func) {
                require_once __DIR__ . "/{$class}_{$func}.php";
            }
        }
    }
}
