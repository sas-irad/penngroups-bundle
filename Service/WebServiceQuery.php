<?php

namespace SAS\IRAD\PennGroupsBundle\Service;

use SAS\IRAD\PersonInfoBundle\PersonInfo\PersonInfo;
use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;


class WebServiceQuery {
    
    private $username;
    private $password_file;
    private $passwordStorage;
    
    /**
     * Array of query string parameters to include in web api call
     * @var array
     */
    private $parameters;

    /**
     * the base url of the webservice to be called
     *
     * @access protected
     * @var string
     */
    private $service_url = 'https://medley.isc-seo.upenn.edu/grouperWs/servicesRest/v1_4_000';
    
    /**
     * the additional path to add to the webservice url
     * @var string
     */
    private $service_path;

    /**
     * The result code expected back from the webservice request
     * @var string
     */
    private $expected_result_code = 'SUCCESS';

    /**
     * The curl session to access the web service
     * @var curl_session
     */
    private $session;

    /**
     * The xml result of the last web service query
     * @var \SimpleXMLElement
     */
    private $xml;
    
    
    
    public function __construct(EncryptedFileStorageService $storage, array $params) {
        
        // checks for required params
        foreach ( array('username', 'password_file') as $param ) {
            if ( isset($params[$param]) ) {
                $this->$param = $params[$param];
            } else {
                throw new \Exception("Required WebServiceQuery parameter '$param' is missing.");
            }
        }

        // file storage for encrypted password
        $this->passwordStorage = $storage->init($this->password_file);        
        
        // parameters for web service call
        $this->parameters = array();
        
        // our web service resource. initialize it only when needed
        $this->session = false;        
    }
    
    public function __destruct() {
        if ( $this->session ) {
            curl_close($this->session);
        }
    }
    
    
    /**
     * Perform a web service query matching on penn_id. Return an array
     * of attributes for match or false if no result.
     * @param string $penn_id
     * @return array
     */
    public function findByPennID($penn_id) {

        if ( !preg_match('/^\d{8}$/', $penn_id) ) {
            throw new \Exception("Invalid penn_id passed.");
        }

        $this->setServicePath('subjects');
        
        $this->setParam('wsLiteObjectType', 'WsRestGetSubjectsLiteRequest');
        $this->setParam('subjectId', $penn_id);
        
        $this->execute();
        $subjects = $this->getSubjectsFromResult();
        
        if ( $subjects ) {
            // convert first result to PersonInfo object
            return new PersonInfo($subjects[0]);
        }
        
        return false;
    }
    

    /**
     * Perform a webservice query matching on pennkey. Return an array
     * of attributes for match or false if no result.
     * @param string $pennkey
     * @return array
     */
    public function findByPennkey($pennkey) {
    
        if ( !preg_match('/^[A-Za-z][A-Za-z0-9]{1,16}$/', $pennkey) ) {
            throw new \Exception("Invalid pennkey passed.");
        }

        $this->setServicePath('subjects');
        
        $this->setParam('wsLiteObjectType', 'WsRestGetSubjectsLiteRequest');
        $this->setParam('subjectIdentifier', $pennkey);
        
        $this->execute();
        $subjects = $this->getSubjectsFromResult();
        
        if ( $subjects ) {
            // convert first result to PersonInfo object
            return new PersonInfo($subjects[0]);
        }
        
        return false;
    }
    
    /**
     * Return an array of members in the given penngroup path
     * @param string $path Path for penngroup
     * @return array
     */
    public function getGroupMembers($path) {
        
        $this->setServicePath('groups/' . urlencode($path) . '/members');
        $this->execute();
        $subjects = $this->getSubjectsFromResult();
        
        return ( count($subjects) > 0 ? $subjects : false);
    }

    /**
     * Return an array of groups where $penn_id has membership
     * @param string $penn_id Penn ID to query
     * @return array
     */
    public function getGroups($penn_id) {

        if ( !preg_match('/^\d{8}$/', $penn_id) ) {
            throw new \Exception("Invalid penn_id passed.");
        }
        
        $this->setServicePath("subjects/$penn_id/groups");

        $this->execute();
        $groups = $this->getGroupsFromResult();
        
        return ( count($groups) > 0 ? $groups : false);
    }
    
    /**
     * Test if a given subject (penn_id) is a member of the given penngroup
     * @param string $path Path of penngroup
     * @param string $penn_id Penn ID of subject to test
     * @return boolean
     */
    public function isMemberOf($path, $penn_id) {

        if ( !preg_match('/^\d{8}$/', $penn_id) ) {
            throw new \Exception("Invalid penn_id passed.");
        }
        
        $this->setServicePath("groups/" . urlencode($path) . "/members/$penn_id");
        $this->execute();

        // check result code in query results
        return ( (string) $this->xml->resultMetadata->resultCode === 'IS_MEMBER' ) ;
    }
    
    /**
     * Set a parameter for our web api call
     * @param string $param_name
     * @param string $param_value
     */
    private function setParam($param_name, $param_value) {
        $this->parameters[$param_name] = $param_value;
    }

    /**
     * Set a parameter for our web api call
     * @param string $param_name
     * @param string $param_value
     */
    private function setServicePath($service_path) {
        $this->service_path = $service_path;
    }    
    
    
    /**
     * Return a query string version of our api parameters
     * @return string
     */
    private function getQueryString() {
        
        $units = array();
        foreach ( $this->parameters as $key => $value ) {
            $units[] = urlencode($key) . '=' . urlencode($value);
        }

        return implode('&', $units);
    }
    
    /**
     * Return/initialize curl session for web service query
     * @return curl_session
     */
    private function getSession() {
        // initialize session if we haven't already
        if ( !$this->session ) {
            $this->session = curl_init();
            
            curl_setopt($this->session, CURLOPT_HTTPHEADER, array('Content-type: text/xml;charset="utf-8"'));
            curl_setopt($this->session, CURLOPT_HTTPGET, true);
            curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
            
            // These were previously set to false to get a valid ssl connection (in case this breaks elsewhere)
            curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->session, CURLOPT_SSL_VERIFYHOST, 2);
            
            // set authentication header
            curl_setopt($this->session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->session, CURLOPT_USERPWD, $this->username . ":" . $this->passwordStorage->get());
        }
        return $this->session;
    }
    
    /**
     * execute the call to the webservice
     *
     * @access protected
     * @throws \Exception
     */
    protected function execute() {
    
        // we only want "human" results
        $this->setParam('sourceIds', 'pennperson');
        
        // set the attributes we want returned
        $this->setParam('subjectAttributeNames', 'PENNNAME,EMAIL,FIRST_NAME,LAST_NAME');
        
        // build url for rest api call
        $url = implode('/', array($this->service_url, $this->service_path)) . '?' . $this->getQueryString();
        curl_setopt($this->getSession(), CURLOPT_URL, $url);
        
        //do the call
        $response = curl_exec($this->getSession());

        if ( $response === false ) {
            throw new \Exception('Connection Error:' . curl_error($this->getSession()));
        } 

        // did we get back valid xml?
        try {
            $this->xml = simplexml_load_string($response);

        } catch (\Exception $e) {
            throw new \Exception("Webservice did not return valid xml: $response");    
        }
        
        // clear our values after a successful query
        $this->parameters = array();
        $this->setServicePath(null);
    }
    
    /**
     * Check lastest web service query for subjects in the results
     * @throws \Exception
     * @return array
     */
    protected function getSubjectsFromResult() {
    
        $subjects = array();
        
        // did we get a valid query result?
        if ( (string) $this->xml->resultMetadata->resultCode != $this->expected_result_code ) {
            // throw exception on error? (but not empty search results)
            throw new \Exception((string) $this->xml->resultMetadata->resultMessage);
        }
        
        // did we find anything?
        if ( !$this->xml->wsSubjects ) {
            return false;
        }

        // did we have a subject error?
        if ( $this->xml->wsSubjects && (string) $this->xml->wsSubjects->WsSubject->resultCode != $this->expected_result_code ) {
            return false;
        }

        // Do we have attributes to return? Use "pennkey" instead of "pennname" for attribute name.
        $attribute = array();
        $index = 0;
        if ( $this->xml->subjectAttributeNames->string ) {
            foreach ( $this->xml->subjectAttributeNames->string as $attribName ) {
                if ( $attribName == 'PENNNAME' ) {
                    $attribName = 'pennkey';
                }
                $attribute[$index] = strtolower($attribName);
                $index++;
            }
        }

        // convert xml subject results to array
        foreach ( $this->xml->wsSubjects->WsSubject as $subject ) {

            $person = array('penn_id' => (string) $subject->id);
            
            if ( $subject->name ) {
                $person['name'] = (string) $subject->name;
            }
            
            foreach ( $attribute as $index => $name ) {
                $person[$name] = (string) $subject->attributeValues->string[$index];
            }
            
            // if "name" is empty, build it from first/last
            if ( !isset($person['name']) && isset($person['last_name']) ) {
                $person['name'] = $person['first_name'] . ' ' . $person['last_name'];
            }
            
            $subjects[] = $person;
        }
        
        return $subjects;
    }

    
    /**
     * Check lastest web service query for groups in the results
     * @throws \Exception
     */
    protected function getGroupsFromResult() {

        // did we get a valid query result?
        if ( (string) $this->xml->resultMetadata->resultCode != $this->expected_result_code ) {
            // throw exception on error? (but not empty search results)
            throw new \Exception((string) $this->xml->resultMetadata->resultMessage);
        }
        
        // did we find anything?
        if ( !$this->xml->wsGroups ) {
            return false;
        }
        
        // convert xml group results to array (if we have any)
        $groups = array();
        
        foreach ( $this->xml->wsGroups->WsGroup as $group ) {
            $groups[(string) $group->uuid] = (string) $group->name;
        }
        
        return $groups;
    }
}