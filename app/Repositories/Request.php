<?php
    
    
    namespace App\Repositories;
    
    
    abstract class Request
    {
        public function __get($property)
        {
            // TODO: Implement __get() method.
            return $this->$property;
        }
        
        public function __set($property, $value)
        {
            // TODO: Implement __set() method.
            return $this->$value;
        }
    }
