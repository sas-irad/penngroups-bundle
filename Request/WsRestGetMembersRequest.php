<?php

namespace SAS\IRAD\PennGroupsBundle\Request;

/**
 * The request object to query members of a penngroup
 * @author robertom
 */
class WsRestGetMembersRequest extends WsRestAbstractRequest {

    /**
     * Query parameter which specifies which members to include
     * in query results: all, immediate, nonimmediate, etc.
     * @var array
     */
    private $memberFilter;
    
    /**
     * Array of legal values for passing to setMemberFilter()
     * @var array
     */
    private $legalFilters;
    
    /**
     * Array of member attributes to return from query
     * @var array
     */
    private $subjectAttributeNames;
    
    /**
     * The group id/uuid that we want to query
     * @var string
     */
    private $group;

    /**
     * Query parameter to specify the group in the query
     * @var array
     */
    private $wsGroupLookups;
    
    
    /**
     * Set default options for request object
     */
    public function __construct() {
        
        // default filter
        $this->memberFilter = array('memberFilter' => 'All');
        
        // legal memberFilter values
        $this->legalFilters = array('All',
                                    'Effective',
                                    'Immediate',
                                    'NonImmediate',
                                    'Composite');
                    
        // attributes to return for subjects
        $attributes = array('name', 'first_name', 'last_name', 'email', 'pennname');
        $this->subjectAttributeNames = array('subjectAttributeNames' => $attributes);
        
        // This will be set to the group we want to query. If it is still false
        // when getSomeRequest() is called, we through an exception
        $this->group = false;
        
        // set this properties from the abstract parent class
        $this->servicePath = 'groups';
        $this->expectedResponseType = 'SAS\IRAD\PennGroupsBundle\Response\WsGetMembersResultsResponse';        
    }
    
    /**
     * Set the member filter for the query
     * @param string $filter
     */
    public function setMemberFilter($filter) {

        if ( !in_array($filter, $this->legalFilters) ) {
            throw new \Exception("MemberFilter \"$filter\" is invalid. Valid options are: " . implode(", ", $this->legalFilters));
        }
        $this->memberFilter['memberFilter'] = $filter;
        
        return $this;
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
     * Set the group we want to query
     * @param string $group
     */
    public function setGroup($group) {
        
        $this->group = $group;
        
        // what we want to find. do we have a uuid or name?
        if ( preg_match("/^[a-f0-9]{32}$/", $this->group) ) {
            $queryParam = array('uuid' => $this->group);
        } else {
            $queryParam = array('groupName' => $this->group);
        }
        $this->wsGroupLookups = array('wsGroupLookups' => array($queryParam));
        
        return $this;
    }

    
    /**
     * Return the array of all request parameters.
     * @return array
     */
    public function getRequestParams() {

        if ( $this->group === false ) {
            throw new \Exception("Call setGroup() before generating the request object");
        }
        
        return array('WsRestGetMembersRequest' =>
                    array_merge($this->memberFilter,
                                $this->subjectAttributeNames,
                                $this->wsGroupLookups));            
        
    }
}