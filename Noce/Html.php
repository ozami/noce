<?php
namespace Noce;

class Html
{
    const AUTO_CLOSE = "auto";
    const CLOSE = "close";
    const NO_CLOSE = "no";
    const SELF_CLOSE = "self";
    
    public $_tag = "";
    public $_children = array();
    public $_attributes = array();
    public $_close = self::AUTO_CLOSE;
    
    public function __construct($x = null)
    {
        // 移行用
        if ($x !== null) {
            throw new \Exception("incompatible construction");
        }
    }
    
    public function __toString()
    {
        return $this->getEscapedHtml();
    }
    
    public function getEscapedHtml()
    {
        if ($this->_tag == "") {
            return $this->getEscapedChildren();
        }
        $html = "<" . $this->_tag;
        $attributes = $this->getEscapedAttributes();
        if ($attributes != "") {
            $html .= " " . $attributes;
        }
        $close = $this->calcClose();
        if ($close == self::SELF_CLOSE) {
            $html .= " />";
        }
        else {
            $html .= ">" . $this->getEscapedChildren();
            if ($close == self::CLOSE) {
                $html .= "</$this->_tag>";
            }
        }
        return $html;
    }
    
    public function getEscapedChildren()
    {
        $html = "";
        foreach ($this->_children as $child) {
            if (method_exists($child, "getEscapedHtml")) {
                $html .= $child->getEscapedHtml();
            }
            else {
                $html .= self::escape($child);
            }
        }
        return $html;
    }
    
    public function getEscapedAttributes()
    {
        $html = array();
        foreach ($this->_attributes as $name => $value) {
            $html[] = $name . '="' . self::escape($value) . '"';
        }
        return join(" ", $html);
    }
    
    public function append($content)
    {
        if ($content != "") {
            $this->_children[] = $content;
        }
        return $this;
    }
    
    public function prepend($content)
    {
        if ($content != "") {
            array_unshift($this->_children, $content);
        }
        return $this;
    }
    
    public function tag($tag)
    {
        $tag = "$tag";
        // TODO: support characters defined in <http://www.w3.org/TR/xml11/#NT-NameStartChar>
        if (preg_match("/[^-:A-Za-z0-9_.]/", $tag)) {
            throw new \InvalidArgumentException();
        }
        $this->_tag = $tag;
        return $this;
    }
    
    public function attr($name, $value)
    {
        $name = "$name";
        // TODO: support characters defined in <http://www.w3.org/TR/xml11/#attdecls>
        if (preg_match("/[^-:A-Za-z0-9_.]/", $name)) {
            throw new \InvalidArgumentException();
        }
        if ($name == "") {
            throw new \InvalidArgumentException();
        }
        $this->_attributes[$name] = $value;
        return $this;
    }
    
    public function attrs(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->attr($name, $value);
        }
        return $this;
    }
    
    public function id($id)
    {
        $this->attr("id", $id);
        return $this;
    }
    
    public function style($name, $value)
    {
        $this->attr("style", ltrim(@$this->_attributes["style"] . " $name: $value;"));
        return $this;
    }
    
    public function styles(array $styles)
    {
        foreach ($styles as $name => $value) {
            $this->style($name, $value);
        }
        return $this;
    }

    public function addClass($class_name)
    {
        $this->attr("class", ltrim(@$this->_attributes["class"] . " $class_name"));
        return $this;
    }
    
    public function close($close)
    {
        $this->_close = $close;
        return $this;
    }
    
    public function calcClose()
    {
        if ($this->_close != self::AUTO_CLOSE) {
            return $this->_close;
        }
        static $empties = array( // TODO: confirm
            "area", "base", "br", "col", "command", "embed", 
            "hr", "img", "input", "keygen", "link", "meta", 
            "param", "source", "track", "wbr"
        );
        if (in_array($this->_tag, $empties)) {
            return self::SELF_CLOSE;
        }
        return self::CLOSE;
    }
    
    public static function h($s = "")
    {
        $h = new Html();
        $h->append($s);
        return $h;
    }
    
    public static function p($s)
    {
        if (method_exists($s, "getEscapedHtml")) {
            echo $s->getEscapedHtml();
        }
        else {
            echo self::escape($s);
        }
    }
    
    public static function escape($s)
    {
        // TODO: should we reject non UTF-8 code?
        return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8");
    }
    
    public static function aTag($href = "", array $attrs = array())
    {
        if ($href != "") {
            $attrs["href"] = $href;
        }
        if (@$attrs["target"] == "_auto_blank") {
            if (preg_match("#^https?:#", $href)) {
                $attrs["target"] = "_blank";
            }
            else {
                unset($attrs["target"]);
            }
        }
        return self::h()->tag("a")->attrs($attrs);
    }
    
    public static function optionTag(array $options, $selected = array())
    {
        $html = new Html();
        $selected = (array) $selected;
        foreach ($options as $value => $label) {
            $a = array("value" => $value);
            if (in_array("$value", $selected)) {
                $a["selected"] = "selected";
            }
            $html->append(self::h()->tag("option")->attrs($a)->append($label));
        }
        return $html;
    }
    
    public function nl2br()
    {
        $converted = array();
        foreach ($this->_children as $c) {
            if (method_exists($c, "nl2br")) {
                $converted[] = $c->nl2br();
            }
            else {
                $offset = 0;
                while (preg_match("/([^\r\n]*)(\r\n|\r|\n)/u", $c, $matches, 0, $offset)) {
                    $converted[] = $matches[1];
                    $converted[] = self::h()->tag("br");
                    $converted[] = $matches[2];
                    $offset += strlen($matches[0]);
                }
                $remainder = substr($c, $offset);
                if ($remainder != "") {
                    $converted[] = $remainder;
                }
            }
        }
        $this->_children = $converted;
        return $this;
    }
    
    public static function linkStyles()
    {
        $styles = array();
        for ($path = "style.css", $i = count(explode("/", $_SERVER["SCRIPT_NAME"])); $i > 1; --$i, $path = "../$path") {
            if (is_readable($path)) {
                array_unshift($styles, $path);
            }
        }
        foreach ($styles as $style) {
            printf(
            '<link rel="stylesheet" type="text/css" href="%s" />',
            urlencode($style));
        }
    }
    
    public static function jsData($name, $value)
    {
        $js = urlencode(json_encode($value));
        $js = "var $name = JSON.parse(decodeURIComponent('$js'.replace(/[+]/g, ' ')));";
        return self::h()
            ->tag("script")
            ->attrs(array("type" => "text/javascript"))
            ->close(self::CLOSE)
            ->append(new Html_Cdata($js));
    }
    
    public static function pageScript()
    {
        $js = preg_replace("/\\.[^.]*$/", "", basename($_SERVER["SCRIPT_NAME"])) . ".js";
        $dir = dirname($_SERVER["SCRIPT_FILENAME"]);
        if (!is_readable("$dir/$js")) {
            return;
        }
        printf(
        '<script type="text/javascript" src="%s"></script>', 
        urlencode($js));
    }
}
