<?php
namespace Noce;

class Validator_Image
{
    public function validate($value, $input)
    {
        $size = getimagesize($value->path);
        $format = (array) $input->getFormat();
        if ($format && !in_array($size[2], $format)) {
            return "err_invalid_image_format";
        }
        // max / min width
        // max / min height
    }
}
