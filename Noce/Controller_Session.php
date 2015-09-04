<?php
namespace Noce;

class Controller_Session extends Controller
{
    public $_session;
    
    public function act($action)
    {
        $this->startSession($action);
        return parent::act($action);
    }
    
    public function startSession($action)
    {
        $this->_session =& Session::get(get_class($this));
        if ($this->doesActionRequireSession($action) && $this->_session === null) {
            $this->noSession($action);
        }
    }
    
    public function doesActionRequireSession($action)
    {
        return true;
    }
    
    public function destroySession()
    {
        $this->_session = null;
    }
}
