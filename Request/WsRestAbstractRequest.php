<?php

namespace SAS\IRAD\PennGroupsBundle\Request;


abstract class WsRestAbstractRequest {
    
    protected $expectedResponseType;
    protected $servicePath;
    
    /**
     * What type of object should a successful query return?
     * @return string
     */    
    public function getExpectedResponseType() {
        if ( !$this->expectedResponseType ) {
            throw new \Exception("You must define the expectedResponseType property in the request class");
        }        
        return $this->expectedResponseType;
    }    
    
    /**
     * Return the service path for the api call
     * @return string
     */    
    public function getServicePath() {
        if ( !$this->servicePath ) {
            throw new \Exception("You must define the servicePath property in the request class");
        }
        return $this->servicePath;
    }     
    
    /**
     * Return a json request object
     * @return string
     */
    public function getJsonRequest() {
        return json_encode($this->getRequestParams());
    }    

    abstract protected function getRequestParams();
    
}