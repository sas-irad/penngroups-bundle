<?php 

namespace SAS\IRAD\PennGroupsBundle\Response;


class WsGetMembersResultsResponse extends WsAbstractResultsResponse {
    
    public function __construct($json) {
        $this->responseType = 'WsGetMembersResults';
        parent::__construct($json);
    }
    
    /**
     * Where do we find wsSubjects in this result?
     * @return array
     */    
    protected function getWsSubjects() {
        
        if ( !isset($this->result['results']) || 
                !isset($this->result['results'][0]) || 
                !isset($this->result['results'][0]['wsSubjects']) ) {
            throw new \Exception("wsSubjects not found in response");            
        }
        
        return $this->result['results'][0]['wsSubjects'];
    }
    
    protected function getWsGroups() {
        return false;
    }    
}