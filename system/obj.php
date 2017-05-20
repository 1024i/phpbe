<?php
namespace system;

abstract class obj
{
    protected $_errors = [];

    public function set_error($error)
    {
        $this->_errors[] = $error;
    }

    public function get_error()
    {
        if (count($this->_errors) > 0) {
            return $this->_errors[0];
        }
        return false;
    }

    public function get_errors()
    {
        return $this->_errors;
    }
    
    public function has_error()
    {
        return count($this->_errors) > 0;
    }

    public function clear_errors()
    {
        $this->_errors = array();
    }
}