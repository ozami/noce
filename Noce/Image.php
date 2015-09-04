<?php
namespace Noce;

class Image
{
    const JPEG = "jpeg";
    const PNG = "png";
    const GIF = "gif";
    
    public $_gd;

    public function __construct($file)
    {
        $args = func_get_args();
        if (count($args) == 2) {
            $this->_gd = imagecreatetruecolor($args[0], $args[1]);
        }
        else if (count($args) == 1 && is_resource($args[0])) {
            $this->_gd = $args[0];
        }
        else if (count($args) == 1) {
            $this->open($args[0]);
        }
        else {
            throw new \LogicException();
        }
    }

    public function __destruct()
    {
        $this->destroy();
    }

    public function destroy()
    {
        if ($this->_gd) {
            @imagedestroy($this->_gd);
            $this->_gd = null;
        }
    }

    public function open($file)
    {
        $this->destroy();
        list ($w, $h, $type) = @getimagesize($file);
        if ($type === null) {
            throw new \Exception("err_unknown_image_type");
        }
        $loader = array(
            IMAGETYPE_GIF => "imagecreatefromgif",
            IMAGETYPE_JPEG => "imagecreatefromjpeg",
            IMAGETYPE_JPEG2000 => "imagecreatefromjpeg",
            IMAGETYPE_PNG => "imagecreatefrompng",
            IMAGETYPE_BMP => "imagecreatefromwbmp",
            IMAGETYPE_WBMP => "imagecreatefromwbmp");
        $loader = $loader[$type];
        if (!$loader) {
            throw new \Exception("err_unknown_image_type");
        }
        $this->_gd = $loader($file);
    }
    
    public function save($file, $type, $options)
    {
        imageinterlace($this->_gd, (bool) @$options["interlace"]);
        if ($type == self::JPEG) {
            $o = $options + array(
                "quality" => 80
            );
            if (!imagejpeg($this->_gd, $file, $o["quality"])) {
                throw new \RuntimeException();
            }
            return;
        }
        if ($type == self::PNG) {
            $o = $options + array(
                "quality" => 9
            );
            if (!imagepng($this->_gd, $file, $o["quality"])) {
                throw new \RuntimeException();
            }
            return;
        }
        if ($type == self::GIF) {
            if (!imagegif($this->_gd, $file)) {
                throw new \RuntimeException();
            }
            return;
        }
        throw new \LogicException();
    }

    public function getSize()
    {
        return array($this->getWidth(), $this->getHeight());
    }

    public function getWidth()
    {
        return imagesx($this->_gd);
    }

    public function getHeight()
    {
        return imagesy($this->_gd);
    }

    public function resize($width, $height)
    {
        $dst = imagecreatetruecolor($width, $height);
        if (!$dst) {
            throw new \Exception("err_image_resize");
        }
        $w = $this->getWidth();
        $h = $this->getHeight();
        $r = imagecopyresampled(
            $dst, $this->_gd,
            0, 0, 0, 0,
            $width, $height, $w, $h
        );
        if (!$r) {
            throw new \RuntimeException("err_image_resize");
        }
        imagedestroy($this->_gd);
        $this->_gd = $dst;
        return $this;
    }
    
    public function scale($x, $y = null)
    {
        if ($y === null) {
            $y = $x;
        }
        return $this->resize(
            ceil($this->getWidth() * $x),
            ceil($this->getHeight() * $y)
        );
    }

    public function inscribe($width, $height)
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        $ratio = min($width / $w, $height / $h);
        return $this->scale($ratio);
    }
    
    public function cover($width, $height)
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        $ratio = max($width / $w, $height / $h);
        return $this->scale($ratio);
    }
}
