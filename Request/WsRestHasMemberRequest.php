<?php

namespace SAS\IRAD\PennGroupsBundle\Request;


/**
 * The request object to query subjects
 * @author robertom
 */
class WsRestHasMemberRequest  extends WsRestAbstractRequest {
    
    /**
     * The group we are querying
     * @var string
     */
    private $group = false;
    
    /**
     * The subject query parameter
     * @var array
     */
    private $subjectLookups = false;
    
    
    public function __construct() {
        // set this properties from the abstract parent class
        $this->servicePath = 'subjects';
        $this->expectedResponseType = 'SAS\IRAD\PennGroupsBundle\Response\WsHasMemberResultsResponse';
    }

    /**
     * Set the subject we want to query
     * @param string $subject_id
     */
    public function setSubjectId($subject_id) {
        $this->subjectLookups = array('subjectLookups' => array(array('subjectId' => $subject_id)));
        return $this;
    }
    
    /**
     * Set the groupt we want to query
     * @param string $group
     */
    public function setGroup($group) {
        $this->group = $group;
        $this->servicePath = "groups/" . urlencode($group) . "/members";
        return $this;
    }     

    /**
     * Return the array of all request parameters.
     * @return array
     */
    protected function getRequestParams() {

        if ( $this->subjectLookups === false ) {
            throw new \Exception("Call setSubjectId() before generating the request object");
        }
        
        if ( $this->group === false ) {
            throw new \Exception("Call setGroup() before generating the request object");
        }        
        
        return array('WsRestHasMemberRequest' => $this->subjectLookups);
    }
}