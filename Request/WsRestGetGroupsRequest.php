<?php

namespace SAS\IRAD\PennGroupsBundle\Request;

/**
 * The request object to query members of a penngroup
 * @author robertom
 */
class WsRestGetGroupsRequest extends WsRestAbstractRequest {

    /**
     * The subject id we are querying
     * @var string
     */
    private $subject_id;
    
    /**
     * The subject query parameter
     * @var array
     */
    private $subjectLookups;    
    
    /**
     * Array of member attributes to return from query
     * @var array
     */
    private $subjectAttributeNames;    
    
    public function __construct() {

        // the subject_id must be set before call the get*Request method
        $this->subject_id = false;
        
        // attributes to return for subjects
        $attributes = array('description');
        $this->subjectAttributeNames = array('subjectAttributeNames' => $attributes);
        
        // set this properties from the abstract parent class
        $this->servicePath = 'subjects';
        $this->expectedResponseType = 'SAS\IRAD\PennGroupsBundle\Response\WsGetGroupsResultsResponse';
    }
    
    public function setSubjectId($subject_id) {
        $this->subject_id = $subject_id;
        $this->subjectLookups = array('subjectLookups' => array(array('subjectId' => $subject_id)));        
    }

    /**
     * Return the array of all request parameters.
     * @return array
     */
    protected function getRequestParams() {

        if ( $this->subject_id === false ) {
            throw new \Exception("Call setSubjectId() before generating the request object");
        }
        
        return array('WsRestGetGroupsRequest' =>
                    array_merge($this->subjectAttributeNames,
                                $this->subjectLookups));            
        
    }    
}