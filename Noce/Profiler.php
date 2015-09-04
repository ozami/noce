<?php
namespace Noce;

class Profiler
{
    public static $_data = array();
    public static $_last_id = 0;
    public static $_total;

    public function __construct()
    {
        $trace = debug_backtrace();
        $trace = $trace[0];
        $this->_id = ++self::$_last_id;
        self::$_data[$this->_id] = array(
            "file" => $trace["file"], 
            "line" => $trace["line"], 
            "start" => microtime(true));
    }

    public function __destruct()
    {
        self::$_data[$this->_id]["end"] = microtime(true);
    }

    public static function init()
    {
        if (self::$_total === null) {
            register_shutdown_function(array(__CLASS__, "report"));
        }
        self::$_total = microtime(true);
    }

    public static function report()
    {
        $now = microtime(true);

        $total = $now - self::$_total;

        $report = array();
        foreach (self::$_data as $d) {
            $location = Debug::shortPath($d["file"]) . ":" . $d["line"];
            if (!isset($report[$location])) {
                $report[$location] = array("time" => 0.0, "count" => 0);
            }
            $report[$location]["time"] += (isset($d["end"])? $d["end"]: $now) - $d["start"];
            $report[$location]["count"] += 1;
        }
        uksort($report, array(__CLASS__, "compareLocation"));

        $path_length = 0;
        foreach (array_keys($report) as $i) {
            $path_length = max(strlen($i), $path_length);
        }
        ++$path_length;

        $count_length = 0;
        foreach ($report as $data) {
            $count_length = max(strlen($data["count"]), $count_length);
        }

        $out = $_SERVER[isset($_SERVER["REQUEST_URI"])? "REQUEST_URI": "SCRIPT_NAME"] . "\n";
        $format = "%-{$path_length}s  %{$count_length}s %7.2f  %3d%%  %s\n";
        $out .= sprintf($format, "Total", "", $total * 1000, 100, str_repeat("■", 100 / 2));
        foreach ($report as $i => $data) {
            list ($path, $line) = explode(":", $i);
            $location = sprintf("%s(%d)", $path, $line);
            $percent = round(100 * $data["time"] / $total);
            $out .= sprintf($format, $location, $data["count"], $data["time"] * 1000, $percent, str_repeat("■", round($percent / 2)));
        }
        $out .= "Memory Peak " . number_format(memory_get_peak_usage() / 1024) . " KB\n";
        Debug::write(Debug::DEBUG, $out, "Profile");
    }

    public static function compareLocation($l, $r)
    {
        list ($lPath, $lLine) = explode(":", $l);
        list ($rPath, $rLine) = explode(":", $r);
        $c = strnatcmp($lPath, $rPath);
        if ($c) {
            return $c;
        }
        return (int) $lLine - (int) $rLine;
    }
}
