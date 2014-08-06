<?php

namespace SAS\IRAD\PennGroupsBundle\Tests\PennGroups;

use PHPUnit_Framework_TestCase;
use SAS\IRAD\PennGroupsBundle\Service\LDAPQuery;


class LDAPQueryTest extends PHPUnit_Framework_TestCase {
    
    private function setupLdap() {
        global $globalParams;
        return new LDAPQuery($globalParams);
    }
    
    /**
     * Test constructor with no parameters specified
     */
    public function testConstructor1a() {
    
        try {
            $ldap = new LDAPQuery();
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
            $ldap = new LDAPQuery('hello');
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
            $ldap = new LDAPQuery($params);
        } catch (\Exception $e) {
            // yeah! missing username param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with insufficient parameters");
    }
    
    
    /**
     * Test missing "credential" parameter in constructor
     */
    public function testConstructor2() {
    
        $params = array('username' => 'username');
    
        try {
            $ldap = new LDAPQuery($params);
        } catch (\Exception $e) {
            // yeah! missing credential param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with insufficient parameters");
    }
    
    /**
     * Test missing "key" parameter in constructor
     */
    public function testConstructor3() {
    
        $params = array('username'   => 'username',
                        'credential' => '../Resources/private.pem');
    
        try {
            $ldap = new LDAPQuery($params);
        } catch (\Exception $e) {
            // yeah! missing "key" param throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with insufficient parameters");
    }


    /**
     * Pass all parameters, but invalid file paths
     */
    public function testConstructor4() {
    
        $params = array('username'   => 'username',
                        'credential' => '/bogus/credential/path',
                        'key'        => '/bogus/key/path');
    
        try {
            $ldap = new LDAPQuery($params);
        } catch (\Exception $e) {
            // yeah! invalid file paths throws exception
            return;
        }
    
        $this->fail("Expected an exception when calling LDAPQuery constructor with invalid file path parameters");
    }    
    
    /**
     * This should be a valid constructor call
     */
    public function testConstructor5() {
    
        global $globalTestDir;
        
        $params = array('username'   => 'username',
                        'credential' => "$globalTestDir/Resources/private.pem",
                        'key'        => "$globalTestDir/Resources/pw.txt");
    
        try {
            $ldap = new LDAPQuery($params);
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->fail("Constructor test with valid parameter tests failed.");
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

    
    public function testValidPennkey() {
    
        $ldap = $this->setupLdap();
    
        $result = $ldap->findByPennkey('robertom');
    
        $this->assertEquals("10078969", $result->getPennId());
        $this->assertEquals("robertom", $result->getPennkey());
    }    
}