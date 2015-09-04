<?php
namespace Noce;

class Message
{
    public static $messages = array();
    public static $language = "en";
    public static $fallback_language = "en";
    
    public $id;
    public $data;
    
    public function __construct($id, $data = null)
    {
        if ($id instanceof \Exception) {
            $this->id = $id->getMessage();
            if (isset($id->data)) {
                $this->data = $id->data;
            }
        }
        else {
            $this->id = $id;
            $this->data = $data;
        }
    }
    
    public function __toString()
    {
        return $this->string();
    }
    
    public function string()
    {
        $view = new Template(self::get($this->id));
        return $view->string($this->data);
    }
    
    public static function init(array $messages, $language = "en", $fallback_language = "en")
    {
        self::setMessages($messages);
        self::setLanguage($language);
        self::setFallbackLanguage($fallback_language);
    }
    
    public static function get($id)
    {
        $t = self::$messages;
        foreach (array(self::$language, self::$fallback_language) as $l) {
            if (isset($t[$l]) && isset($t[$l][$id])) {
                return $t[$l][$id];
            }
        }
        return $id;
    }
    
    public static function setMessages(array $messages)
    {
        self::$messages = $messages;
    }
    
    public static function setLanguage($language)
    {
        self::$language = $language;
    }
    
    public static function setFallbackLanguage($language)
    {
        self::$fallback_language = $language;
    }
}
