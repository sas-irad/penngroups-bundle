<?php

namespace SAS\IRAD\PennGroupsBundle\Response;


abstract class WsAbstractResultsResponse {
    
    protected $json;
    protected $result;
    protected $responseType;
    
    public function __construct($json) {

        $this->json = $json;
        
        // did we get back valid json?
        $response = json_decode($json, true);
        
        if ( !$this->responseType ) {
            throw new \Exception("You must set responseType property in request class");
        }
        
        if ( $response === null ) {
            throw new \Exception("Webservice did not return valid json: $json");    
        }
        
        // is this the expected result type?
        if ( !isset($response[$this->responseType]) ) {
            throw new \Exception("Expected response \"{$this->responseType}\" was not returned by server.");
        }
        
        $this->result = $response[$this->responseType];
        
        // did we get a valid query result?
        if ( $this->result['resultMetadata']['resultCode'] != 'SUCCESS' ) {
            // throw exception on error? (but not empty search results)
            throw new \Exception($this->result['resultMetadata']['resultMessage']);
        }        
    }
    
    abstract protected function getWsSubjects();
    abstract protected function getWsGroups();

    /**
     * Return the subjects found in the response
     * @return array
     */
    public function getSubjects() {
        
        $subjects = array();

        $wsSubjects = $this->getWsSubjects();
        
        // do we have any results?
        if ( !is_array($wsSubjects) || count($wsSubjects) === 0 ) {
            return false;
        }

        // Do we have attributes to return? Use "pennkey" instead of "pennname" for attribute name.
        $attribute = array();
        $index = 0;
        if ( isset($this->result['subjectAttributeNames']) &&  is_array($this->result['subjectAttributeNames'])) {
            foreach ( $this->result['subjectAttributeNames'] as $attribName ) {
                if ( $attribName == 'pennname' ) {
                    $attribName = 'pennkey';
                }
                $attribute[$index] = strtolower($attribName);
                $index++;
            }
        }

        // convert xml subject results to array
        foreach ( $wsSubjects as $wsSubject ) {
            
            if ( $wsSubject['resultCode'] === 'SUBJECT_NOT_FOUND' ) {
                continue;
            }

            $subject = array('subject_id' => $wsSubject['id'],
                             'source_id'  => $wsSubject['sourceId']);
            
            // is subject_id a penn_id?
            if ( preg_match("/^\d{8}$/", $wsSubject['id']) ) {
                $subject['penn_id'] = $wsSubject['id'];
            }
            
            if ( isset($wsSubject['name']) ) {
                $subject['name'] = $wsSubject['name'];
            }
            
            foreach ( $attribute as $index => $name ) {
                $subject[$name] = $wsSubject['attributeValues'][$index];
            }
            
            // if "name" is empty, build it from first/last
            if ( !isset($wsSubject['name']) && isset($wsSubject['last_name']) ) {
                $subject['name'] = trim($wsSubject['first_name'] . ' ' . $wsSubject['last_name']);
            }
            
            $subjects[] = $subject;
        }
        
        return $subjects;    
    }    
    
    /**
     * Return the groups found in the response
     * @throws \Exception
     * @return array
     */
    public function getGroups() {

        $groups = array();
        
        $wsGroups = $this->getWsGroups();
        
        // did we find anything?
        if ( !is_array($wsGroups) || count($wsGroups) === 0 ) {
            return false;
        }
        
        // convert xml group results to array (if we have any)
        $groups = array();
        
        foreach ( $wsGroups as $wsGroup ) {
            $groups[$wsGroup['uuid']] = $wsGroup;
        }
        
        return $groups;
    }    
}