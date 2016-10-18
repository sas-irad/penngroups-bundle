<?php

namespace SAS\IRAD\PennGroupsBundle\PersonInfo;

use SAS\IRAD\PersonInfoBundle\PersonInfo\PersonInfo;


class PennGroupsPersonInfo extends PersonInfo {
    
    private $name;
    private $email;
    
    public function __construct($array) {
        parent::__construct($array);
        foreach ( array('email', 'name') as $field ) {
            if ( isset($array[$field]) ) {
                $this->$field = $array[$field];
            }
        }        
    }
    
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
    public function getEmail() {
        return $this->email;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    public function getName() {
        return $this->name;
    }
    
}
