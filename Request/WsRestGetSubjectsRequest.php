<?php

namespace SAS\IRAD\PennGroupsBundle\Request;


/**
 * The request object to query subjects
 * @author robertom
 */
class WsRestGetSubjectsRequest  extends WsRestAbstractRequest {
    
    /**
     * Array of member attributes to return from query
     * @var array
     */
    private $subjectAttributeNames;
    
    /**
     * The subject we are querying
     * @var string
     */
    private $subject;
    
    /**
     * The subject query parameter
     * @var array
     */
    private $wsSubjectLookups;
    
    
    public function __construct() {

        // the subject must be set before call the get*Request method
        $this->subject = false;
        
        // attributes to return for subjects
        $attributes = array('name', 'first_name', 'last_name', 'email', 'pennname');
        $this->subjectAttributeNames = array('subjectAttributeNames' => $attributes);
        
        // set this properties from the abstract parent class
        $this->servicePath = 'subjects';
        $this->expectedResponseType = 'SAS\IRAD\PennGroupsBundle\Response\WsGetSubjectsResultsResponse';
    }

    /**
     * Set the subject attributes that will be returned in the query
     * @param array $attributes
     */
    public function setSubjectAttributes($attributes) {
        
        if ( !is_array($attributes) ) {
            throw new \Exception("setSubjectAttributes() requires an array argument");
        }
        
        $this->subjectAttributeNames['subjectAttributeNames'] = $attributes;
    }
    
    
    /**
     * Set the subject we want to query
     * @param string $subjectParam
     */
    public function setSubject($subject) {
        
        $this->subject = $subject;
        
        // what we want to find. do we have a pennkey, penn_id or some other subject id?
        if ( preg_match("/^[a-z][a-z0-9]{2,16}$/", $this->subject) ) {
            $subjectParam = array('subjectIdentifier' => $this->subject);
        } else {
            $subjectParam = array('subjectId' => $this->subject);
        }
        $this->wsSubjectLookups = array('wsSubjectLookups' => array($subjectParam));
        
        return $this;
    }    
        
    /**
     * Return the array of all request parameters.
     * @return array
     */
    protected function getRequestParams() {

        if ( $this->subject === false ) {
            throw new \Exception("Call setSubject() before generating the request object");
        }
        
        return array('WsRestGetSubjectsRequest' =>
                    array_merge($this->subjectAttributeNames,
                                $this->wsSubjectLookups));            
        
    }
}