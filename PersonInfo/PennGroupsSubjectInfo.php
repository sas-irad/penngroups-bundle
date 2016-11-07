<?php

namespace SAS\IRAD\PennGroupsBundle\PersonInfo;

use SAS\IRAD\PersonInfoBundle\PersonInfo\PersonInfo;


class PennGroupsSubjectInfo extends PersonInfo {
    
    private $name;
    private $email;
    private $subject_id;
    private $source_id;
    
    public function __construct($array) {
        parent::__construct($array);
        foreach ( array('email', 'name', 'subject_id', 'source_id') as $field ) {
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
    
    public function getSubjectId() {
        return $this->subject_id;
    }
    
    public function getSourceId() {
        return $this->source_id;
    }
    
    public function isPerson() {
        return $this->source_id === 'pennperson';
    }
    
    public function isGroup() {
        return $this->source_id === 'g:gsa';
    }
    
}
