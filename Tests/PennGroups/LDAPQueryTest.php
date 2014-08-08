<?php

namespace SAS\IRAD\PennGroupsBundle\Tests\PennGroups;

use PHPUnit_Framework_TestCase;
use SAS\IRAD\PennGroupsBundle\Service\LDAPQuery;
use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;


class LDAPQueryTest extends PHPUnit_Framework_TestCase {
    
    private function setupLdap() {
        global $globalParams;
        return new LDAPQuery($this->setupStorage(), $globalParams);
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
            $ldap = new LDAPQuery($this->setupStorage());
        } catch (\Exception $e) {
            // yeah! missing params throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with insufficient parameters");
    }
    
    
    /**
     * Test constructor with non-array parameter specified
     */
    public function testConstructor1b() {
    
        try {
            $ldap = new LDAPQuery($this->setupStorage(), 'hello');
        } catch (\Exception $e) {
            // yeah! non array throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with scalar parameter");
    }    
    
    
    /**
     * Test missing "username" parameter in constructor
     */
    public function testConstructor1c() {
    
        $params = array();
    
        try {
            $ldap = new LDAPQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            // yeah! missing username param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with insufficient parameters");
    }
    
    
    /**
     * Test missing "password_file" parameter in constructor
     */
    public function testConstructor2() {
    
        $params = array('username' => 'username');
    
        try {
            $ldap = new LDAPQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            // yeah! missing password_file param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with insufficient parameters");
    }
    
    /**
     * Pass all parameters, but invalid file path
     */
    public function testConstructor3() {
    
        $params = array('username'      => 'username',
                        'password_file' => '/bogus/password/file/path');
    
        try {
            $ldap = new LDAPQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            // yeah! invalid file paths throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with invalid file path parameters");
    }    
    
    /**
     * This should be a valid constructor call
     */
    public function testConstructor4() {
    
        global $globalTestDir;
        
        $params = array('username'      => 'username',
                        'password_file' => "$globalTestDir/Resources/penngroups.txt");
    
        try {
            $ldap = new LDAPQuery($this->setupStorage(), $params);
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->fail("Constructor test with valid parameters failed.");
        }
        
        $this->assertTrue(is_object($ldap), "LDAPQuery constructor did not return an object.");
    }
    
    /**
     * A penn_id argument which doesn't match 99999999 should throw an exception
     */
    public function testInvalidPennId() {

        $ldap = $this->setupLdap();
        
        try {
            $result = $ldap->findByPennID('*invalid*penn*id*');
        } catch (\Exception $e) {
            // yeah! bogus penn_id threw exception
            return;
        }
        
        $this->fail("Expected an exception from an invalid penn_id");
    }
    

    public function testValidPennId() {
    
        $ldap = $this->setupLdap();
    
        $result = $ldap->findByPennID('10078969');
    
        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
    }

    
    /**
     * A pennkey argument which doesn't match expected format should throw an exception
     */
    public function testInvalidPennkey() {

        $ldap = $this->setupLdap();
        
        try {
            $result = $ldap->findByPennID('*invalid*pennkey*');
        } catch (\Exception $e) {
            // yeah! bogus pennkey threw exception
            return;
        }
        
        $this->fail("Expected an exception from an invalid pennkey");
    }

    
    public function testNoPennIdResult() {
    
        $ldap = $this->setupLdap();
    
        $result = $ldap->findByPennID('00112233');
        $this->assertEquals(false, $result);
    }    
    
    
    public function testValidPennkey() {
    
        $ldap = $this->setupLdap();
    
        $result = $ldap->findByPennkey('robertom');
    
        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
    }
    
    
    public function testNoPennkeyResult() {
    
        $ldap = $this->setupLdap();
    
        $result = $ldap->findByPennkey('root');
        $this->assertEquals(false, $result);
    }
}