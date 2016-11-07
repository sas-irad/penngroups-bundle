<?php

namespace SAS\IRAD\PennGroupsBundle\Service;

use SAS\IRAD\PennGroupsBundle\PersonInfo\PennGroupsSubjectInfo;
use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;
use SAS\IRAD\PennGroupsBundle\Request\WsRestAbstractRequest;
use SAS\IRAD\PennGroupsBundle\Request\WsRestGetGroupsRequest;
use SAS\IRAD\PennGroupsBundle\Request\WsRestGetMembersRequest;
use SAS\IRAD\PennGroupsBundle\Request\WsRestGetSubjectsRequest;
use SAS\IRAD\PennGroupsBundle\Request\WsRestHasMemberRequest;
use SAS\IRAD\PennGroupsBundle\Response\WsAbstractResultsResponse;


class WebServiceQuery {
    
    private $username;
    private $password_file;
    private $passwordStorage;
    
    /**
     * the base url of the webservice to be called
     *
     * @access protected
     * @var string
     */
    private $service_url = 'https://medley.isc-seo.upenn.edu/grouperWs/servicesRest/v2_2_000';
    
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
     * Perform a web service query matching on subject id. Return an array
     * of attributes for match or false if no result.
     * @param string $subject_id
     * @return array
     */
    public function getSubject($subject_id) {

        $request = new WsRestGetSubjectsRequest();
        $request->setSubject($subject_id);
        
        $response = $this->execute($request);

        $subject = $response->getFirstSubject();
        
        if ( $subject ) {
            // convert first result to PersonInfo object
            return new PennGroupsSubjectInfo($subject);
        }
        
        return false;
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

        return $this->getSubject($penn_id);
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

        return $this->getSubject($pennkey);
    }
    
    /**
     * Return an array of members in the given penngroup path
     * @param string $group Path/uuid for penngroup
     * @param string $memberFilter Filter for member query (All, Immediate, etc.)
     * @return array
     */
    public function getGroupMembers($group, $memberFilter = 'All') {

        $request = new WsRestGetMembersRequest();
        $request->setGroup($group)
            ->setMemberFilter($memberFilter);
        
        $response = $this->execute($request);
        $subjects = $response->getSubjects();
        
        return ( count($subjects) > 0 ? $subjects : false);
    }

    /**
     * Return an array of groups where $penn_id has membership
     * @param string $subject_id SubjectId to query
     * @return array
     */
    public function getGroups($subject_id) {

        $request = new WsRestGetGroupsRequest();
        $request->setSubjectId($subject_id);

        $response = $this->execute($request);
        $groups = $response->getGroups();
        
        return ( count($groups) > 0 ? $groups : false);
    }
    
    /**
     * Simple group list function when full group details are not
     * required
     * @param string $subject_id SubjectId to query
     * @return array
     */
    public function getGroupsList($subject_id) {

        $groups = $this->getGroups($subject_id);
        if ( !$groups ) {
            return false;
        }
        
        $list = array();
        foreach ( $groups as $uuid => $group ) {
            $list[$uuid] = $group['name'];
        }
        
        return $list;
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
        
        $request = new WsRestHasMemberRequest();
        $request->setSubjectId($penn_id)
            ->setGroup($path);
        
        $response = $this->execute($request);

        return $response->isMember();
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
            curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
            
            // These were previously set to false to get a valid ssl connection (in case this breaks elsewhere)
            curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->session, CURLOPT_SSL_VERIFYHOST, 2);
            
            // set authentication header
            curl_setopt($this->session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->session, CURLOPT_USERPWD, $this->username . ":" . $this->passwordStorage->get());
            
            // make requests via POSTs with JSON 
            curl_setopt($this->session, CURLOPT_POST, true);
            curl_setopt($this->session, CURLOPT_HTTPHEADER, array('Content-Type: text/x-json'));
        }
        return $this->session;
    }
    
    /**
     * execute the call to the webservice
     *
     * @access protected
     * @throws \Exception
     * @return WsAbstractResultsResponse
     */
    protected function execute(WsRestAbstractRequest $request) {
    
        // build url for rest api call
        $url = implode('/', array($this->service_url, $request->getServicePath()));
        curl_setopt($this->getSession(), CURLOPT_URL, $url);

        // encode parameters in JSON body
        curl_setopt($this->getSession(), CURLOPT_POSTFIELDS, $request->getJsonRequest());

        // do the call
        $result = curl_exec($this->getSession());

        if ( $result === false ) {
            throw new \Exception('Connection Error:' . curl_error($this->getSession()));
        } 

        $responseType = $request->getExpectedResponseType();
        $response = new $responseType($result);
        
        return $response;
    }

}