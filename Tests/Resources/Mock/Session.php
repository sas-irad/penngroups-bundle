<?php

namespace Symfony\Component\HttpFoundation\Session;

class Session {
    
    // array for "session" data
    private $array;
    
    public function __construct() {
        $this->array = array();
    }
    
    public function set($key, $value) {
        $this->array[$key] = $value;
    }
    
    public function get($key) {
        if ( isset($this->array[$key]) ) {
            return $this->array[$key];
        }
        return null;
    }
    
}