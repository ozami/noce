<?php
namespace Noce;

class NoAbort
{
    public function __construct()
    {
        $this->_abort = ignore_user_abort(true);
    }

    public function __destruct()
    {
        ignore_user_abort($this->_abort);
    }
}
