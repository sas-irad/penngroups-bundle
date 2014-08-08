<?php

$globalTestDir = __DIR__;

$globalParams = array('username'      => "irad-authz/sas.upenn.edu",
                      'password_file' => "$globalTestDir/Resources/penngroups.txt");

$globalStorageParams = array( 'keys' => 
                        array('public'  => "$globalTestDir/Resources/public.pem",
                              'private' => "$globalTestDir/Resources/private.pem"));

require_once "../vendor/sas-irad/person-info-bundle/PersonInfo/PersonInfoInterface.php";
require_once "../vendor/sas-irad/person-info-bundle/PersonInfo/PersonInfo.php";
require_once "../vendor/sas-irad/file-storage-bundle/Service/EncryptedFileStorageService.php";
require_once "../vendor/sas-irad/file-storage-bundle/Storage/EncryptedFileStorage.php";
require_once "../vendor/sas-irad/file-storage-bundle/Storage/FileStorage.php";
require_once "../Service/LDAPQuery.php";
require_once "../Service/WebServiceQuery.php";
require_once "../Service/PennGroupsQueryCache.php";
require_once "./Resources/Mock/Session.php";
require_once "../Utility/Unlock.php";