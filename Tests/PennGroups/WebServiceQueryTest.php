<?php

namespace SAS\IRAD\PennGroupsBundle\Tests\PennGroups;

use PHPUnit_Framework_TestCase;
use SAS\IRAD\PennGroupsBundle\Service\WebServiceQuery;
use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;


class WebServiceQueryTest extends PHPUnit_Framework_TestCase {
    
    private function setupWs() {
        global $globalParams;
        return new WebServiceQuery($this->setupStorage(), $globalParams);
    }
    
    private function setupStorage() {
        global $globalStorageParams;
        return new EncryptedFileStorageService($globalStorageParams);
    }    
    
    /**
     * Test constructor with no parameters specified
     */
    public function testConstructor1a() {
    
        try {
            $wsQuery = new WebServiceQuery($this->setupStorage());
        } catch (\Exception $e) {
            // yeah! missing params throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling WebServiceQuery constructor with insufficient parameters");
    }
    
    
    /**
     * Test constructor with non-array parameter specified
     */
    public function testConstructor1b() {
    
        try {
            $wsQuery = new WebServiceQuery($this->setupStorage(), 'hello');
        } catch (\Exception $e) {
            // yeah! non array throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling WebServiceQuery constructor with scalar parameter");
    }    
    
    
    /**
     * Test missing "username" parameter in constructor
     */
    public function testConstructor1c() {
    
        $params = array();
    
        try {
            $wsQuery = new WebServiceQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            // yeah! missing username param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling WebServiceQuery constructor with insufficient parameters");
    }
    
    
    /**
     * Test missing "password_file" parameter in constructor
     */
    public function testConstructor2() {
    
        $params = array('username' => 'username');
    
        try {
            $wsQuery = new WebServiceQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            // yeah! missing password_file param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling WebServiceQuery constructor with insufficient parameters");
    }
    
    /**
     * Pass all parameters, but invalid file path
     */
    public function testConstructor3() {
    
        $params = array('username'      => 'username',
                        'password_file' => '/bogus/password/file/path');
    
        try {
            $wsQuery = new WebServiceQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            // yeah! invalid file paths throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling WebServiceQuery constructor with invalid file path parameters");
    }    
    
    /**
     * This should be a valid constructor call
     */
    public function testConstructor4() {
    
        global $globalTestDir;
        
        $params = array('username'      => 'username',
                        'password_file' => "$globalTestDir/Resources/penngroups.txt");
    
        try {
            $wsQuery = new WebServiceQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->fail("Constructor test with valid parameters failed.");
        }
        
        $this->assertTrue(is_object($wsQuery), "WebServiceQuery constructor did not return an object.");
    }
    
    /**
     * A penn_id argument which doesn't match 99999999 should throw an exception
     */
    public function testInvalidPennId() {

        $wsQuery = $this->setupWs();
        
        try {
            $result = $wsQuery->findByPennID('*invalid*penn*id*');
        } catch (\Exception $e) {
            // yeah! bogus penn_id threw exception
            return;
        }
        
        $this->fail("Expected an exception from an invalid penn_id");
    }
    

    public function testValidPennId() {
    
        $wsQuery = $this->setupWs();
    
        $result = $wsQuery->findByPennID('10078969');
    
        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
        $this->assertEquals("Roberto",  $result->getFirstName());
        $this->assertEquals("Mansfield", $result->getLastName());
    }


    public function testNoPennIdResult() {
    
        $wsQuery = $this->setupWs();
    
        $result = $wsQuery->findByPennID('00112233');
        $this->assertEquals(false, $result);
    }    
    
    
    /**
     * A pennkey argument which doesn't match expected format should throw an exception
     */
    public function testInvalidPennkey() {

        $wsQuery = $this->setupWs();
        
        try {
            $result = $wsQuery->findByPennID('*invalid*pennkey*');
        } catch (\Exception $e) {
            // yeah! bogus pennkey threw exception
            return;
        }
        
        $this->fail("Expected an exception from an invalid pennkey");
    }

    
    public function testValidPennkey() {
    
        $wsQuery = $this->setupWs();
    
        $result = $wsQuery->findByPennkey('robertom');

        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
        $this->assertEquals("Roberto",  $result->getFirstName());
        $this->assertEquals("Mansfield", $result->getLastName());
    }

    public function testNoPennkeyResult() {
    
        $wsQuery = $this->setupWs();
    
        $result = $wsQuery->findByPennkey('root');
        $this->assertEquals(false, $result);
    }    
}