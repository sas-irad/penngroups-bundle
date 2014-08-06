<?php

$globalTestDir = __DIR__;

$globalParams = array('username'   => "irad-authz/sas.upenn.edu",
                      'credential' => "$globalTestDir/Resources/pw.txt",
                      'key'        => "$globalTestDir/Resources/private.pem");

require_once "../vendor/sas-irad/google-admin-client-bundle/Service/PersonInfoInterface.php";
require_once "../vendor/sas-irad/google-admin-client-bundle/Service/PersonInfo.php";
require_once "../Service/LDAPQuery.php";
require_once "../Service/WebServiceQuery.php";
require_once "../Service/PennGroupsQueryCache.php";
require_once "./Resources/Mock/Session.php";
require_once "../Utility/Unlock.php";