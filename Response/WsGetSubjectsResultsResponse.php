<?php

namespace SAS\IRAD\PennGroupsBundle\Response;


class WsGetSubjectsResultsResponse extends WsAbstractResultsResponse {
    
    public function __construct($json) {
        $this->responseType = 'WsGetSubjectsResults';
        parent::__construct($json);
    }

    /**
     * Where do we find wsSubjects in this result?
     * @return array
     */
    protected function getWsSubjects() {
        
        if ( !isset($this->result['wsSubjects']) ) {
            throw new \Exception("wsSubjects not found in response");
        }
        
        return $this->result['wsSubjects'];
    }
   
    protected function getWsGroups() {
        return false;
    }
    
    public function getFirstSubject() {
        $subjects = $this->getSubjects();
        if ( $subjects ) {
            return $subjects[0];
        }
        return false;
    }
}