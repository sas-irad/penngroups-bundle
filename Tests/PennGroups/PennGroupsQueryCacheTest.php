<?php

namespace SAS\IRAD\PennGroupsBundle\Tests\PennGroups;

use PHPUnit_Framework_TestCase;
use SAS\IRAD\PennGroupsBundle\Service\WebServiceQuery;
use SAS\IRAD\PennGroupsBundle\Service\PennGroupsQueryCache;
use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;
// mock session
use Symfony\Component\HttpFoundation\Session\Session;


class PennGroupsQueryCacheTest extends PHPUnit_Framework_TestCase {
    
    private $session;
    
    private function setupQueryCache() {
        global $globalParams;
        global $globalStorageParams;
        $ws = new WebServiceQuery(new EncryptedFileStorageService($globalStorageParams), $globalParams);
        $this->session = new Session();
        // expire cache after 2 seconds for test purposes
        return new PennGroupsQueryCache($this->session, $ws, array('cache_timeout' => 2));
    }
    
    
    public function testQueryCache() {
    
        $queryCache = $this->setupQueryCache();
    
        $result = $queryCache->findByPennID('10078969');
    
        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
        $this->assertEquals("Roberto",  $result->getFirstName());
        $this->assertEquals("Mansfield", $result->getLastName());
        
        // verify that result is stored in cache
        $cache = $this->session->get("penngroups/penn_id/10078969");
        
        $this->assertEquals("10078969", $cache['data']->getPennId());
        $this->assertEquals("robertom", $cache['data']->getPennkey());
        $this->assertEquals("Roberto",  $cache['data']->getFirstName());
        $this->assertEquals("Mansfield", $cache['data']->getLastName());

        // update cache and verify update is returned by $queryCache
        $cache['data']->setPennkey('edwing');
        $cache['data']->setFirstName('Edwin');
        $cache['data']->setLastName('Garcia');
        $this->session->set("penngroups/penn_id/10078969", $cache);
        
        // result should pull from cache
        $result = $queryCache->findByPennID('10078969');

        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("edwing",   $result->getPennkey());
        $this->assertEquals("Edwin",    $result->getFirstName());
        $this->assertEquals("Garcia",   $result->getLastName());
        
        sleep(2);
        
        // the cache has timed out, query should pull fresh data now
        $result = $queryCache->findByPennID('10078969');
        
        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
        $this->assertEquals("Roberto",  $result->getFirstName());
        $this->assertEquals("Mansfield", $result->getLastName());
        
        
        // test other methods now that we "trust" the caching
        $result = $queryCache->findByPennkey('robertom');

        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
        $this->assertEquals("Roberto",  $result->getFirstName());
        $this->assertEquals("Mansfield", $result->getLastName());
        
        $result = $queryCache->getGroupMembers('test:testGroup');
        
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
        // use Chris Hyzer as test case
        $this->assertEquals('10021368', $result[0]['penn_id']);        
        
        $result = $queryCache->getGroups('10021368');
        
        $this->assertTrue(is_array($result));
        $this->assertTrue(in_array('test:testGroup', $result));
        
        // check groups for robertom
        $result = $queryCache->getGroups('10078969');
        
        $this->assertTrue(is_array($result));
        $this->assertTrue(in_array('penn:community:employeeNonTemp', $result));
        
        // check group membership
        $result = $queryCache->isMemberOf('test:testGroup', '10021368');
        $this->assertTrue($result);
        
        $result = $queryCache->isMemberOf('test:testGroup', '10078969');
        $this->assertFalse($result);
    }
}