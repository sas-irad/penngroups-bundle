<?php 

namespace SAS\IRAD\PennGroupsBundle\Response;


class WsGetGroupsResultsResponse extends WsAbstractResultsResponse {
    
    public function __construct($json) {
        $this->responseType = 'WsGetGroupsResults';
        parent::__construct($json);
    }
    
    protected function getWsSubjects() {
        return false;
    }
    
    /**
     * Where do we find wsSubjects in this result?
     * @return array
     */    
    protected function getWsGroups() {
        
        if ( !isset($this->result['results']) || 
                !isset($this->result['results'][0]) || 
                !isset($this->result['results'][0]['wsGroups']) ) {
            throw new \Exception("wsGroups not found in response");            
        }
        
        return $this->result['results'][0]['wsGroups'];
    }

}