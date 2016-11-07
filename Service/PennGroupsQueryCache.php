<?php

namespace SAS\IRAD\PennGroupsBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;


/**
 * This is basically a wrapper around WebServiceQuery, but we use the session
 * to cache information. Adjust cache lifetime with penngroups.cache_timeout in 
 * parameters.yml
 * 
 * @author robertom
 */

class PennGroupsQueryCache {
    
    private $cache_timeout;
    private $session;
    private $webService;
    
    public function __construct(Session $session, WebServiceQuery $webService, $params) {
        
        if ( !isset($params['cache_timeout']) ) {
            throw new \Exception("Required option 'cache_timeout' not specified for PennGroupsQueryCache");
        }

        if ( !is_integer($params['cache_timeout']) ) {
            throw new \Exception("Required option 'cache_timeout' for PennGroupsQueryCache must be an integer");
        }
        
        $this->cache_timeout = $params['cache_timeout'];
        $this->session       = $session;
        $this->webService    = $webService;
    }
    
    public function findByPennkey($pennkey) {
        return $this->find('findByPennkey', "penngroups/pennkey/$pennkey", $pennkey);
    }
    
    public function findByPennID($penn_id) {
        return $this->find('findByPennID', "penngroups/penn_id/$penn_id", $penn_id);
    }    
    
    public function getGroupMembers($path, $memberFilter = 'All') {
        return $this->find('getGroupMembers', "penngroups/group/$path", $path, $memberFilter);
    }

    public function getGroups($penn_id) {
        return $this->find('getGroups', "penngroups/groupMemberships/$penn_id", $penn_id);
    }

    public function getGroupsList($penn_id) {
        return $this->find('getGroupsList', "penngroups/groupMembershipsList/$penn_id", $penn_id);
    }
    
    public function isMemberOf($path, $penn_id) {
        return $this->find('isMemberOf', "penngroups/isMemberOf/$path/$penn_id", $path, $penn_id);
    }
    
    
    private function find($method, $key, $arg1, $arg2 = null) {
        
        if ( $cache = $this->session->get($key) ) {
            if ( $cache['expires_on'] > time() ) {
                return $cache['data'];
            }
        }
        
        $data = $this->webService->$method($arg1, $arg2);
        $info = array('expires_on' => time() + $this->cache_timeout,
                      'data'       => $data);
        
        $this->session->set($key, $info);
        
        return $data;
    }
    
}