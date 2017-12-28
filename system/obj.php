<?php
namespace system;

abstract class Obj
{
    protected $errors = [];

    public function setError($error)
    {
        $this->errors[] = $error;
    }

    public function getError()
    {
        if (count($this->errors) > 0) {
            return $this->errors[0];
        }
        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }
    
    public function hasError()
    {
        return count($this->errors) > 0;
    }

    public function clearErrors()
    {
        $this->errors = array();
    }
}