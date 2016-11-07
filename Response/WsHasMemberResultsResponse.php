<?php 

namespace SAS\IRAD\PennGroupsBundle\Response;


class WsHasMemberResultsResponse extends WsAbstractResultsResponse {
    
    public function __construct($json) {
        $this->responseType = 'WsHasMemberResults';
        parent::__construct($json);
    }

    public function isMember() {
        return $this->result['results'][0]['resultMetadata']['resultCode'] === 'IS_MEMBER';
    }
    
    protected function getWsSubjects() {
        return false;
    }
    
    protected function getWsGroups() {
       return false;
    }
}