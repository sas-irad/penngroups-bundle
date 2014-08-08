<?php

namespace SAS\IRAD\PennGroupsBundle\Service;

use SAS\IRAD\PersonInfoBundle\PersonInfo\PersonInfo;
use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;

class LDAPQuery {
    
    private $passwordStorage;
    private $username;
    private $password_file;
    private $key;
    private $ldap;
    private $filter;
    
    public function __construct(EncryptedFileStorageService $storage, array $params) {

        // checks for required params
        foreach ( array('username', 'password_file') as $param ) {
            if ( isset($params[$param]) ) {
                $this->$param = $params[$param];
            } else {
                throw new \Exception("Required LDAPQuery parameter '$param' is missing.");
            }
        }
        
        // file storage for encrypted password
        $this->passwordStorage = $storage->init($this->password_file);
        
        // the filter attribute is used in the contruction of the LDAP query
        $this->filter = array();
        
        // our ldap resource. initialize it only when needed
        $this->ldap = false;
    }
    
    
    public function __destruct() {
        if ( $this->ldap ) {
            // close ldap
            ldap_unbind($this->ldap);
        }
    }
    
    
    /**
     * Perform an ldap query matching on penn_id. Return an array
     * of attributes for match or false if no result.
     * @param string $penn_id
     * @return array
     */
    public function findByPennID($penn_id) {

        if ( !preg_match('/^\d{8}$/', $penn_id) ) {
            throw new \Exception("Invalid penn_id passed.");
        }
        
        $this->setFilter('pennid', $penn_id);
        $result = $this->execute();
        
        return $result;
    }
    

    /**
     * Perform an ldap query matching on pennkey. Return an array
     * of attributes for match or false if no result.
     * @param string $pennkey
     * @return array
     */
    public function findByPennkey($pennkey) {
    
        if ( !preg_match('/^[A-Za-z][A-Za-z0-9]{1,16}$/', $pennkey) ) {
            throw new \Exception("Invalid pennkey passed.");
        }
        
        $this->setFilter('pennname', $pennkey);
        $result = $this->execute();
        
        return $result;
    }
    
    
    private function getLdap() {
        // initialize connection if we haven't already
        if ( !$this->ldap ) {

            // settings for ldap connection
            $pg_server = 'penngroups.upenn.edu';
            $bind_dn   = "uid={$this->username},ou=entities,dc=upenn,dc=edu";
            
            // uncomment for detailed debugging
            // ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
            $this->ldap = ldap_connect($pg_server);
            
            if ( !$this->ldap ) {
                throw new \Exception("ldap_connect failed: " . ldap_error($this->ldap));
            }
            
            ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
            
            if ( !ldap_start_tls($this->ldap) ) {
                throw new \Exception("ldap_start_tls failed: " . ldap_error($this->ldap));
            }
            
            // get password ready
            $password = $this->passwordStorage->get();
            
            if ( !ldap_bind($this->ldap, $bind_dn, $password) ) {
                throw new \Exception("ldap_bind failed: "  . ldap_error($this->ldap));
            }            
        }
        return $this->ldap;
    }
    
    private function execute() {

        $ldap = $this->getLdap();
        
        // settings for ldap query
        $base_dn = 'ou=pennnames,dc=upenn,dc=edu';
        $attrs   = array('pennname', 'pennid');

        // construct our search filter
        $filter  = $this->getQueryFilter();
        
        // run ldap query
        $results = ldap_search($ldap, $base_dn, $filter, $attrs);
        
        if ( !$results ) {
            // search failed with error
            throw new \Exception("ldap_search failed: " . ldap_error($ldap));
        }

        // extract entries from $results resource
        $entries = ldap_get_entries($ldap, $results);
        if ( $entries === false ) {
            throw new \Exception("ldap_get_entries failed: " . ldap_error($ldap));
        }
        
        if ( $entries['count'] === 0 ) {
            // valid query, but nothing found
            return false;
        }

        if ( $entries['count'] > 1 ) {
            // we didn't get a unique result
            throw new \Exception("Error: ldap returned multiple entries when only one expected.");
        }
    
        // so we should have a single unique result at this point
        $result = array('penn_id' => $entries[0]['pennid'][0],
                        'pennkey' => $entries[0]['pennname'][0] );

        // clear our query filter
        $this->filter = array();

        return new PersonInfo($result);
    }


    /**
     * Sets a filter value in our filter array. Appropriate keys are pennname and pennid.
     * @param string $key
     * @param string $value
     */
    private function setFilter($key, $value) {
        $this->filter[$key] = $value;
    }
    
    
    /**
     * Construct the LDAP filter string for either penn_id or pennkey search
     */
    private function getQueryFilter() {
        
        $units = array();
        foreach ( $this->filter as $key => $value) {
            $units[] = "$key=$value";
        }
        
        $filter = implode(',', $units);
        
        return "(&($filter)(objectClass=*))";
    }
    
}