<?php
namespace Noce;

class Debug
{
    const NONE    = "0";
    const ERROR   = "3error";
    const WARNING = "4warning";
    const NOTICE  = "5notice";
    const INFO    = "6info";
    const DEBUG   = "7debug";
    // Pseudo levels
    const PRODUCTION = "production";
    const DEVELOPMENT = "development";

    public static $_display_lv = self::NONE;
    public static $_log_lv = self::INFO;
    public static $_log;
    public static $_root;
    
    public static function init(array $config)
    {
        if (isset($config["root_path"])) {
            self::setRootPath($config["root_path"]);
        }
        if (@$config["catch_error"]) {
            self::catchError();
        }
        if (isset($config["level"])) {
            $lv = constant(__CLASS__ . "::" . strtoupper($config["level"]));
            if ($lv !== null) {
                self::setDisplayLevel($lv);
                self::setLogLevel($lv);
            }
        }
        if (isset($config["log_level"])) {
            $log_lv = constant(__CLASS__ . "::" . strtoupper($config["log_level"]));
            if ($log_lv !== null) {
                self::setLogLevel($log_lv);
            }
        }
        if (isset($config["display_level"])) {
            $display_lv = constant(__CLASS__ . "::" . strtoupper($config["display_level"]));
            if ($display_lv !== null) {
                self::setDisplayLevel($display_lv);
            }
        }
        if (isset($config["log"])) {
            $config["log"] += array("rotation" => false);
            $log = new Log(
                $config["log"]["file"],
                $config["log"]["rotation"]
            );
            if (@$config["log"]["truncate"]) {
                $log->truncate();
            }
            self::setLog($log);
        }
    }

    public static function scan($data)
    {
        ob_start();
        var_dump($data);
        $s = ob_get_contents();
        ob_end_clean();
        // Escape special characters within the array keys
        $s = preg_replace_callback(
            '/(\n +\\[)(.*?)(]=>\n)/s', 
            function ($m) {
                return $m[1] . addcslashes($m[2], "\\\r\n\t") . $m[3];
            }, 
            $s);
        // Remove line folding after array key
        $s = preg_replace("/\n( +)\\[(.*)]=>\n +/", "\n$1$2 => ", $s);
        $s = preg_replace("/array\\(0\\) \\{\n *}/", "array(0) {}", $s);
        // Simplify integer value
        $s = preg_replace("/\bint\\(([-0-9]+)\\)\n/", "$1\n", $s);
        // Simplify string value
        do {
            $count = 0;
            $s = preg_replace_callback(
                "/\bstring\\(([0-9]+)\\) \"(.*)/s", 
                function ($m) {
                    return '"' . addcslashes(substr($m[2], 0, $m[1]), "\\\r\n\t") . substr($m[2], $m[1]);
                }, 
                $s, -1, $count);
        } while ($count);
        // Increase indent from 2 to 4
        $s = preg_replace_callback(
            "/^( +)/m", 
            function ($m) {
                return $m[1] . $m[1];
            }, 
            $s);
        return $s;
    }

    public static function error($s)
    {
        self::write(self::ERROR, $s);
        exit();
    }

    public static function warning($s)
    {
        self::write(self::WARNING, $s);
    }

    public static function notice($s)
    {
        self::write(self::NOTICE, $s);
    }

    public static function info($s)
    {
        self::write(self::INFO, $s);
    }

    public static function dbg($s)
    {
        self::write(self::DEBUG, $s);
    }

    public static function setLevel($display_lv, $log_lv = null)
    {
        if ($log_lv === null) {
            $log_lv = $display_lv;
        }
        self::setDisplayLevel($display_lv);
        self::setLogLevel($log_lv);
    }

    public static function setDisplayLevel($lv)
    {
        if ($lv == self::DEVELOPMENT) {
            $lv = self::DEBUG;
        }
        else if ($lv == self::PRODUCTION) {
            $lv = self::NONE;
        }
        self::$_display_lv = $lv;
    }

    public static function setLogLevel($lv)
    {
        if ($lv == self::DEVELOPMENT) {
            $lv = self::DEBUG;
        }
        else if ($lv == self::PRODUCTION) {
            $lv = self::INFO;
        }
        self::$_log_lv = $lv;
    }

    public static function write($lv, $s, $where = null)
    {
        if (!$where) {
            $where = self::getCaller();
        }
        if (is_array($where)) {
            $src = self::shortPath($where['file']) . "({$where['line']})";
            if (isset($where["function"])) {
                $src .= " ";
                if (isset($where["class"])) {
                    $src .= $where["class"] . "::";
                }
                $src .= $where["function"] . "()";
            }
            $where = $src;
        }
        self::display($lv, $s, $where);
        self::log($lv, $s, $where);
    }

    public static function display($lv, $s, $where)
    {
        if ($lv > self::$_display_lv) {
            return;
        }
        if (ini_get("html_errors")) {
            if (!headers_sent()) {
                header("Content-Type: text/html; charset=UTF-8");
            }
            static $colors = array(
                self::ERROR   => "rgba(224, 22, 0, 1)",
                self::WARNING => "rgba(229, 132, 29, 1)",
                self::NOTICE  => "rgba(245, 211, 0, 1)",
                self::INFO    => "rgba(0, 186, 53, 1)",
                self::DEBUG   => "rgba(66, 118, 186, 1)"
            );
            $color = $colors[$lv];
            echo '<span style="background: #fefefe; border: 1px solid ' . $color . '; border-radius: 6px; box-shadow: inset 0px 0px 1px 0px ' . str_replace("1)", "0.25)", $color) . '; color: black; display: block; font-family: monospace; font-size: 12px; line-height: 1.75; margin: 12px; padding: 6px 12px; position: static; text-align: left; white-space: pre-wrap;" title="' . ucfirst(substr($lv, 1)) . '">';
            echo '<span style="float: right; margin: 0 0 0 12px;">' . $where . '</span>';
            echo htmlspecialchars($s, ENT_QUOTES);
            echo '<span style="clear: both; display: block;"></span></span>';
        }
        else {
            echo "[" . ucfirst(substr($lv, 1)) . "] $where:\n$s";
        }
    }

    public static function log($lv, $s, $where)
    {
        if ($lv > self::$_log_lv) {
            return;
        }
        $msg = "[" . ucfirst(substr($lv, 1)) . "] $where:\n$s";
        if (self::$_log) {
            self::$_log->write($msg);
        }
        else {
            @error_log(preg_replace("/[[:space:]]/u", " ", $msg));
        }
    }

    public static function setLog($log)
    {
        self::$_log = $log;
    }

    public static function getCaller()
    {
        $traces = array_reverse(debug_backtrace());
        $debug_file_prefix = substr(__FILE__, 0, -4);
        foreach ($traces as $i => $t) {
            if (isset($t["file"]) && strpos($t["file"], $debug_file_prefix) === 0) {
                break;
            }
        }
        $prev = $traces[$i - 1];
        $r = array(
            "file" => $prev["file"],
            "line" => $prev["line"]);
        if ($i >= 2) {
            $prev = $traces[$i - 2];
            $r += array(
                "function" => @$prev["function"],
                "class" => @$prev["class"]);
        }
        return $r;
    }

    public static function d($data)
    {
        self::write(self::DEBUG, self::scan($data));
        return $data;
    }

    public static function dd($data)
    {
        self::d($data);
        die();
    }

    public static function handleException($e)
    {
        $msg = get_class($e) . ": " . $e->getMessage() . " (" . $e->getCode() . ")";
        $traceData = $e->getTrace();
        $trace = "";
        foreach ($traceData as $t) {
            $trace .= @$t["file"] . "(" . @$t["line"] . ") ";
            if (isset($t["function"])) {
                if (isset($t["class"])) {
                    $trace .= $t["class"] . $t["type"];
                }
                $trace .= $t["function"];
            }
            $trace .= "\n";
        }
        $where = array(
            "file" => $e->getFile(), 
            "line" => $e->getLine());
        if ($traceData && isset($traceData[0]["function"])) {
            $where += ArrayX::pick($traceData[0], array("function", "class", "type"));
        }
        self::write(self::ERROR, "$msg\n$trace", $where);
    }

    public static function catchError()
    {
        set_error_handler(array(__CLASS__, "handleError"));
        register_shutdown_function(array(__CLASS__, "handleFatalError"));
        set_exception_handler(array(__CLASS__, "handleException"));
    }

    public static function handleError($no, $str, $file, $line, $vars)
    {
        static $lvs = array(
            E_ERROR             => self::ERROR,
            E_PARSE             => self::ERROR,
            E_CORE_ERROR        => self::ERROR,
            E_CORE_WARNING      => self::ERROR,
            E_COMPILE_ERROR     => self::ERROR,
            E_COMPILE_WARNING   => self::ERROR,
            E_WARNING           => self::WARNING,
            E_NOTICE            => self::NOTICE,
            E_USER_ERROR        => self::ERROR,
            E_USER_WARNING      => self::WARNING,
            E_USER_NOTICE       => self::NOTICE,
            E_RECOVERABLE_ERROR => self::ERROR,
            E_DEPRECATED        => self::NOTICE,
            E_USER_DEPRECATED   => self::NOTICE,
            E_STRICT            => self::NOTICE);
        if (error_reporting() & $no) {
            self::write($lvs[$no], $str, compact("file", "line"));
        }
        if ($no == E_USER_ERROR) {
            die();
        }
        return true;
    }

    public static function handleFatalError()
    {
        $e = error_get_last();
        if (!in_array($e["type"], array(
            E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING,
            E_COMPILE_ERROR, E_COMPILE_WARNING))) {
            return;
        }
        self::handleError($e["type"], $e["message"], $e["file"], $e["line"], null);
    }

    public static function setRootPath($path)
    {
        self::$_root = explode("/", realpath($path));
    }

    public static function shortPath($abs_path)
    {
        $path = explode("/", realpath($abs_path));
        $root = self::$_root;
        while ($path && $root && $path[0] == $root[0]) {
            array_shift($path);
            array_shift($root);
        }
        $rel_path = str_repeat("../", count($root)) . join("/", $path);
        if (strlen($rel_path) >= strlen($abs_path)) {
            return $abs_path;
        }
        return $rel_path;
    }
}

Debug::setRootPath($_SERVER["DOCUMENT_ROOT"]);
