<?php
namespace Noce;

/**
 *
 * Type of URL:
 *   + Absolute path
 *     + Absolute URL
 *   + Relative path
 *     + Empty Path
 */
class Url
{
    private $scheme = "";
    private $host = "";
    private $port = "";
    private $path = array();
    private $query = array();
    private $fragment = "";

    public function __construct()
    {
        call_user_func_array(array($this, "set"), func_get_args());
    }

    /**
     *
     * $url Url|string|array
     */
    public function set($url, $query = array(), $fragment = "")
    {
        if ($url instanceof Url) {
            $url = $url->toArray();
        }
        else if (!is_array($url)) {
            $url = @parse_url($url);
            if ($url === false) {
                throw new \Exception("Invalid URL '$url'.");
            }
        }
        $url["query"] = $query + (array) @$url["query"];
        if ($fragment != "") {
            $url["fragment"] = $fragment;
        }
        foreach (array("scheme", "host", "port", "path",
                       "query", "fragment") as $name) {
            $this->__set($name, @$url[$name]);
        }
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function setScheme($scheme)
    {
        $this->scheme = (string) $scheme;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = (string) $host;
        $this->setPath($this->path); // Maintain path
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        if (preg_match("/[^0-9]/", $port)) {
            throw new \Exception("Invalid port number '$port'");
        }
        $this->port = $port == ""? "": (int) $port;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPathString()
    {
        $path = $this->path;
        foreach ($path as $i => $p) {
            $path[$i] = urlencode($p);
        }
        return join("/", $path);
    }

    public function getPathEnd()
    {
        return $this->path[count($this->path) - 1];
    }

    public function setPathEnd($name)
    {
        $this->path[count($this->path) - 1] = $name;
    }

    public function appendToPath($name)
    {
        // TODO: Support array
        $this->path[] = $name;
    }

    // TODO: Remove?
    public function getAbsolutePath()
    {
        $path = $this->path;
        if (count($path) == 0 || $path[0] != "") {
            array_unshift($path, "");
        }
        return $path;
    }

    public function setPath($path)
    {
        if (!is_array($path)) {
            $path = explode("/", $path);
            foreach ($path as $i => $p) {
                $path[$i] = urldecode($p);
            }
        }
        // Empty path need to be array("")
        if (!$path) {
            $path = array("");
        }
        $this->path = $path;
        // Force the path to be absolute if host was specified
        // (= if it is an absolute URL)
        if ($this->isAbsolute() && !$this->isAbsolutePath()) {
            array_unshift($this->path, "");
        }
        // Resolve . and .. if the path is absolute
        if ($this->isAbsolutePath()) {
            $this->path = array_diff($this->path, array("."));
            $resolved = array();
            foreach ($this->path as $p) {
                if ($p == "..") {
                    array_pop($resolved);
                    continue;
                }
                $resolved[] = $p;
            }
            $this->path = $resolved;
        }
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getQueryString()
    {
        return http_build_query($this->query);
    }

    public function setQuery($query)
    {
        if (!is_array($query)) {
            $query = self::parseQuery($query);
        }
        $this->query = $query;
    }

    public function getFragment()
    {
        return $this->fragment;
    }
    
    public function setFragment($fragment)
    {
        $this->fragment = (string) $fragment;
    }

    public function __get($name)
    {
        if (!property_exists(__CLASS__, $name)) {
            throw new \Exception("Property '$name' not found");
        }
        $getter = "get" . ucwords($name);
        return $this->$getter();
    }

    public function __set($name, $value)
    {
        if (!property_exists(__CLASS__, $name)) {
            throw new \Exception("Property '$name' not found");
        }
        $setter = "set" . ucwords($name);
        $this->$setter($value);
    }

    public function apply($url)
    {
        $url = self::toObject($url);
        // Absolute URL
        if ($url->isAbsolute()) {
            // Copy all properties
            $this->set($url);
            return;
        }
        // Absolute path
        if ($url->isAbsolutePath()) {
            // Leave scheme, host, port untouched
            $this->setPath($url->path);
            $this->setQuery($url->query);
            $this->setFragment($url->fragment);
            return;
        }
        // Empty path
        if ($url->isEmptyPath()) {
            if ($url->query) {
                $this->setQuery($url->query);
            }
            $this->setFragment($url->fragment);
            return;
        }
        // Relative path
        $path = array_slice($this->path, 0, -1);
        $this->setPath(array_merge($path, $url->path));
        $this->setQuery($url->query);
        $this->setFragment($url->fragment);
    }

    public function isTls()
    {
        $this->scheme == "https";
    }

    public function isAbsolute()
    {
        return $this->host != "";
    }

    public function isAbsolutePath()
    {
        return count($this->path) > 1 && $this->path[0] == ""; 
    }

    public function isRelativePath()
    {
        return !$this->isAbsolutePath();
    }

    public function isEmptyPath()
    {
        return count($this->path) == 1 && $this->path[0] == "";
    }

    public function isSameHost($url)
    {
        $url = self::toObject($url);
        if ($this->host == $url->host &&
            $this->scheme == $url->scheme &&
            $this->port == $url->port) {
            return true;
        }
        return false;
    }

    // TODO: Review
    public function within($url)
    {
        if (!$this->isSameHost($url)) {
            return false;
        }
        $path = $url->getAbsolutePath();
        foreach ($this->getAbsolutePath() as $i => $p) {
            if ($path[$i] != $p) {
                return false;
            }
        }
        return true;
    }

    public function toString()
    {
        $url = "";
        if ($this->isAbsolute()) {
            // Scheme
            $scheme = $this->scheme == ""? "http": $this->scheme;
            $url  = $scheme . "://";
            // Hostname
            $url .= $this->host;
            // Port number
            $defaultPorts = array("http" => 80, "https" => 443);
            if ($this->port != "" && $this->port != $defaultPorts[$this->scheme]) {
                $url .= ":" . $this->port;
            }
        }
        // Path
        $url .= $this->getPathString($this->path);
        // Query
        $query = self::getQueryString();
        if ($query != "") {
            $url .= "?" . $query;
        }
        // Fragment
        if ($this->fragment != "") {
            $url .= "#" . $this->fragment;
        }
        return $url;
    }

    public function toArray()
    {
        $a = array();
        foreach (array("scheme", "host", "port", "path",
                       "query", "fragment") as $name) {
            $a[$name] = $this->$name;
        }
        return $a;
    }

    public static function toObject($url)
    {
        if ($url instanceof Url) {
            return $url;
        }
        $class = __CLASS__;
        return new $class($url);
    }

    /**
     * Parse and decode query string and return in array
     *
     * @param $query string Query part of the url. Must be URL encoded.
     * @return array 
     */
    public static function parseQuery($query)
    {
        $query = explode("&", $query);
        $parsed = array();
        foreach ($query as $i => $q) {
            @list ($name, $value) = explode("=", $q, 2);
            if ($name == "") {
                continue;
            }
            $name = urldecode($name);
            $value = urldecode($value);
            // Reconstruct array value
            if (preg_match("/^(.*?)\[(.*)]$/u", $name, $matches)) {
                $path = array_merge(array($matches[1]),
                                    explode("][", $matches[2]));
                ArrayX::pathSet($parsed, $path, $value);
            }
            else {
                $parsed[$name] = $value;
            }
        }
        return $parsed;
    }
}
